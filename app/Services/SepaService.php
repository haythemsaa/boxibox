<?php

namespace App\Services;

use App\Models\MandatSepa;
use App\Models\PrelevementSepa;
use App\Models\Facture;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SepaService
{
    private $creditorId;
    private $creditorName;
    private $creditorIban;
    private $creditorBic;

    public function __construct()
    {
        $this->creditorId = config('boxibox.sepa.creditor_id');
        $this->creditorName = config('boxibox.sepa.creditor_name');
        $this->creditorIban = config('boxibox.sepa.creditor_iban');
        $this->creditorBic = config('boxibox.sepa.creditor_bic');
    }

    /**
     * Génère un fichier SEPA pour les factures en attente
     */
    public function generateSepaFile(Carbon $datePrelevement, array $factureIds = [])
    {
        // Récupérer les factures éligibles
        $factures = $this->getFacturesEligibles($factureIds);

        if ($factures->isEmpty()) {
            throw new \Exception('Aucune facture éligible pour le prélèvement SEPA');
        }

        // Créer les prélèvements
        $prelevements = $this->createPrelevements($factures, $datePrelevement);

        // Générer le fichier XML
        $xmlContent = $this->generateXmlContent($prelevements, $datePrelevement);

        // Sauvegarder le fichier
        $fileName = $this->generateFileName($datePrelevement);
        $filePath = "sepa/exports/{$fileName}";

        Storage::put($filePath, $xmlContent);

        // Mettre à jour les prélèvements avec le nom du fichier
        foreach ($prelevements as $prelevement) {
            $prelevement->update(['fichier_sepa' => $fileName]);
        }

        return [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'prelevements_count' => $prelevements->count(),
            'total_amount' => $prelevements->sum('montant')
        ];
    }

    /**
     * Traite les retours SEPA
     */
    public function processSepaReturns(string $returnFileContent)
    {
        $xml = new \SimpleXMLElement($returnFileContent);
        $results = [
            'processed' => 0,
            'rejected' => 0,
            'accepted' => 0
        ];

        // Parser les retours selon le schéma pain.002
        $paymentInfos = $xml->xpath('//pain:PmtInf');

        foreach ($paymentInfos as $paymentInfo) {
            $transactions = $paymentInfo->xpath('.//pain:TxInfAndSts');

            foreach ($transactions as $transaction) {
                $endToEndId = (string) $transaction->EndToEndId;
                $statusCode = (string) $transaction->TxSts;
                $reasonCode = (string) $transaction->StsRsnInf->Rsn->Cd ?? '';
                $reasonText = (string) $transaction->StsRsnInf->AddtlInf ?? '';

                // Trouver le prélèvement correspondant
                $prelevement = PrelevementSepa::where('reference_end_to_end', $endToEndId)->first();

                if ($prelevement) {
                    if ($statusCode === 'RJCT') {
                        $prelevement->update([
                            'statut' => 'rejete',
                            'code_retour' => $reasonCode,
                            'libelle_retour' => $reasonText,
                            'date_retour' => now()
                        ]);
                        $results['rejected']++;

                        // Remettre la facture en attente
                        $prelevement->facture->update(['statut' => 'en_attente']);

                    } else if ($statusCode === 'ACCP' || $statusCode === 'ACSC') {
                        $prelevement->update([
                            'statut' => 'reussi',
                            'date_retour' => now()
                        ]);
                        $results['accepted']++;

                        // Marquer la facture comme payée
                        $prelevement->facture->update([
                            'statut' => 'payee',
                            'date_paiement' => now(),
                            'mode_paiement' => 'prelevement'
                        ]);
                    }

                    $results['processed']++;
                }
            }
        }

        return $results;
    }

    /**
     * Valide un IBAN
     */
    public function validateIban(string $iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));

        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        // Algorithme de validation IBAN
        $checkDigits = substr($iban, 2, 2);
        $accountIdentifier = substr($iban, 4) . substr($iban, 0, 2) . '00';

        $numericString = '';
        for ($i = 0; $i < strlen($accountIdentifier); $i++) {
            $char = $accountIdentifier[$i];
            if (ctype_alpha($char)) {
                $numericString .= (ord($char) - ord('A') + 10);
            } else {
                $numericString .= $char;
            }
        }

        $remainder = '';
        for ($i = 0; $i < strlen($numericString); $i++) {
            $remainder .= $numericString[$i];
            if (strlen($remainder) >= 9) {
                $remainder = bcmod($remainder, '97');
            }
        }

        $calculatedCheckDigits = 98 - intval($remainder);

        return str_pad($calculatedCheckDigits, 2, '0', STR_PAD_LEFT) === $checkDigits;
    }

    /**
     * Génère une référence unique de mandat (RUM)
     */
    public function generateRum(int $clientId): string
    {
        return 'RUM' . str_pad($clientId, 6, '0', STR_PAD_LEFT) . date('YmdHis') . rand(100, 999);
    }

    private function getFacturesEligibles(array $factureIds = [])
    {
        $query = Facture::with(['client.mandatsSepa', 'contrat'])
            ->whereIn('statut', ['emise', 'envoyee'])
            ->where('date_echeance', '<=', now())
            ->whereHas('client.mandatsSepa', function($q) {
                $q->where('is_active', true)
                  ->where('statut', 'valide');
            });

        if (!empty($factureIds)) {
            $query->whereIn('id', $factureIds);
        }

        return $query->get();
    }

    private function createPrelevements($factures, Carbon $datePrelevement)
    {
        $prelevements = collect();

        foreach ($factures as $facture) {
            $mandat = $facture->client->mandatsSepa()
                ->where('is_active', true)
                ->where('statut', 'valide')
                ->first();

            if (!$mandat) {
                continue;
            }

            $prelevement = PrelevementSepa::create([
                'mandat_sepa_id' => $mandat->id,
                'facture_id' => $facture->id,
                'montant' => $facture->montant_ttc,
                'date_prelevement' => $datePrelevement,
                'date_valeur' => $datePrelevement->copy()->addDays(2),
                'statut' => 'envoye'
            ]);

            $prelevement->generateEndToEndReference();
            $prelevement->save();

            $prelevements->push($prelevement);
        }

        return $prelevements;
    }

    private function generateXmlContent($prelevements, Carbon $datePrelevement)
    {
        $messageId = 'MSG' . date('YmdHis') . rand(1000, 9999);
        $paymentId = 'PMT' . date('YmdHis');
        $totalAmount = $prelevements->sum('montant');
        $transactionCount = $prelevements->count();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document></Document>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $cstmrDrctDbtInitn = $xml->addChild('CstmrDrctDbtInitn');

        // Group Header
        $grpHdr = $cstmrDrctDbtInitn->addChild('GrpHdr');
        $grpHdr->addChild('MsgId', $messageId);
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', $transactionCount);
        $grpHdr->addChild('CtrlSum', number_format($totalAmount, 2, '.', ''));

        $initgPty = $grpHdr->addChild('InitgPty');
        $initgPty->addChild('Nm', $this->creditorName);

        // Payment Information
        $pmtInf = $cstmrDrctDbtInitn->addChild('PmtInf');
        $pmtInf->addChild('PmtInfId', $paymentId);
        $pmtInf->addChild('PmtMtd', 'DD');
        $pmtInf->addChild('NbOfTxs', $transactionCount);
        $pmtInf->addChild('CtrlSum', number_format($totalAmount, 2, '.', ''));

        $pmtTpInf = $pmtInf->addChild('PmtTpInf');
        $svcLvl = $pmtTpInf->addChild('SvcLvl');
        $svcLvl->addChild('Cd', 'SEPA');

        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Cd', 'CORE');

        $pmtTpInf->addChild('SeqTp', 'RCUR'); // FRST, RCUR, OOFF, FNAL

        $pmtInf->addChild('ReqdColltnDt', $datePrelevement->format('Y-m-d'));

        // Creditor
        $cdtr = $pmtInf->addChild('Cdtr');
        $cdtr->addChild('Nm', $this->creditorName);

        $cdtrAcct = $pmtInf->addChild('CdtrAcct');
        $id = $cdtrAcct->addChild('Id');
        $id->addChild('IBAN', $this->creditorIban);

        $cdtrAgt = $pmtInf->addChild('CdtrAgt');
        $finInstnId = $cdtrAgt->addChild('FinInstnId');
        $finInstnId->addChild('BIC', $this->creditorBic);

        $pmtInf->addChild('ChrgBr', 'SLEV');

        $cdtrSchmeId = $pmtInf->addChild('CdtrSchmeId');
        $id = $cdtrSchmeId->addChild('Id');
        $prvtId = $id->addChild('PrvtId');
        $othr = $prvtId->addChild('Othr');
        $othr->addChild('Id', $this->creditorId);
        $schmeNm = $othr->addChild('SchmeNm');
        $schmeNm->addChild('Prtry', 'SEPA');

        // Direct Debit Transaction Information
        foreach ($prelevements as $prelevement) {
            $drctDbtTxInf = $pmtInf->addChild('DrctDbtTxInf');

            $pmtId = $drctDbtTxInf->addChild('PmtId');
            $pmtId->addChild('EndToEndId', $prelevement->reference_end_to_end);

            $instdAmt = $drctDbtTxInf->addChild('InstdAmt', number_format($prelevement->montant, 2, '.', ''));
            $instdAmt->addAttribute('Ccy', 'EUR');

            $drctDbtTx = $drctDbtTxInf->addChild('DrctDbtTx');
            $mndtRltdInf = $drctDbtTx->addChild('MndtRltdInf');
            $mndtRltdInf->addChild('MndtId', $prelevement->mandatSepa->rum);
            $mndtRltdInf->addChild('DtOfSgntr', $prelevement->mandatSepa->date_signature->format('Y-m-d'));

            $dbtrAgt = $drctDbtTxInf->addChild('DbtrAgt');
            $finInstnId = $dbtrAgt->addChild('FinInstnId');
            $finInstnId->addChild('BIC', $prelevement->mandatSepa->bic);

            $dbtr = $drctDbtTxInf->addChild('Dbtr');
            $dbtr->addChild('Nm', $prelevement->mandatSepa->titulaire_compte);

            $dbtrAcct = $drctDbtTxInf->addChild('DbtrAcct');
            $id = $dbtrAcct->addChild('Id');
            $id->addChild('IBAN', $prelevement->mandatSepa->iban);

            $rmtInf = $drctDbtTxInf->addChild('RmtInf');
            $rmtInf->addChild('Ustrd', 'Facture ' . $prelevement->facture->numero_facture);
        }

        return $xml->asXML();
    }

    private function generateFileName(Carbon $date)
    {
        return 'SEPA_' . $this->creditorId . '_' . $date->format('Ymd_His') . '.xml';
    }
}