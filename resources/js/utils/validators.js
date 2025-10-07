/**
 * Validateurs personnalisés pour Vuelidate
 * Utilisables dans tout le projet
 */

/**
 * Valide un IBAN français ou européen
 * @param {string} value - L'IBAN à valider
 * @returns {boolean}
 */
export const validateIban = (value) => {
    if (!value) return false;

    // Supprimer les espaces
    const iban = value.replace(/\s/g, '').toUpperCase();

    // Vérifier la longueur (entre 15 et 34 caractères)
    if (iban.length < 15 || iban.length > 34) return false;

    // Vérifier le format (commence par 2 lettres + 2 chiffres)
    if (!/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/.test(iban)) return false;

    // Algorithme de validation IBAN (MOD 97)
    const rearranged = iban.slice(4) + iban.slice(0, 4);
    const numerical = rearranged.split('').map(char => {
        const code = char.charCodeAt(0);
        return code >= 65 && code <= 90 ? code - 55 : char;
    }).join('');

    // Vérifier le checksum MOD 97
    let remainder = numerical;
    while (remainder.length > 2) {
        const block = remainder.slice(0, 9);
        remainder = (parseInt(block, 10) % 97) + remainder.slice(block.length);
    }

    return parseInt(remainder, 10) % 97 === 1;
};

/**
 * Valide un code BIC/SWIFT
 * @param {string} value - Le BIC à valider
 * @returns {boolean}
 */
export const validateBic = (value) => {
    if (!value) return false;

    const bic = value.replace(/\s/g, '').toUpperCase();

    // Format: 8 ou 11 caractères
    // 4 lettres (code banque) + 2 lettres (code pays) + 2 lettres/chiffres (code localisation) + [3 lettres/chiffres optionnels (code branche)]
    return /^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/.test(bic);
};

/**
 * Valide un numéro SIRET français (14 chiffres)
 * @param {string} value - Le SIRET à valider
 * @returns {boolean}
 */
export const validateSiret = (value) => {
    if (!value) return false;

    const siret = value.replace(/\s/g, '');

    // Vérifier que c'est bien 14 chiffres
    if (!/^\d{14}$/.test(siret)) return false;

    // Algorithme de Luhn pour validation SIRET
    let sum = 0;
    for (let i = 0; i < siret.length; i++) {
        let digit = parseInt(siret[i], 10);

        if (i % 2 === 0) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }

        sum += digit;
    }

    return sum % 10 === 0;
};

/**
 * Valide un numéro de téléphone français
 * @param {string} value - Le téléphone à valider
 * @returns {boolean}
 */
export const validatePhoneFR = (value) => {
    if (!value) return false;

    const phone = value.replace(/[\s.-]/g, '');

    // Format français : 10 chiffres commençant par 0
    // Ou format international : +33 suivi de 9 chiffres
    return /^0[1-9]\d{8}$/.test(phone) || /^(\+33|0033)[1-9]\d{8}$/.test(phone);
};

/**
 * Valide un code postal français
 * @param {string} value - Le code postal à valider
 * @returns {boolean}
 */
export const validateCodePostalFR = (value) => {
    if (!value) return false;

    // 5 chiffres, commence par 01-95 (sauf 20 qui est la Corse)
    return /^(0[1-9]|[1-8]\d|9[0-5]|2[AB])\d{3}$/.test(value);
};

/**
 * Valide un email (format standard)
 * @param {string} value - L'email à valider
 * @returns {boolean}
 */
export const validateEmail = (value) => {
    if (!value) return false;

    // Regex email standard (RFC 5322 simplifié)
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
};

/**
 * Valide une URL
 * @param {string} value - L'URL à valider
 * @returns {boolean}
 */
export const validateUrl = (value) => {
    if (!value) return false;

    try {
        new URL(value);
        return true;
    } catch {
        return false;
    }
};

/**
 * Valide un montant (nombre positif avec max 2 décimales)
 * @param {string|number} value - Le montant à valider
 * @returns {boolean}
 */
export const validateAmount = (value) => {
    if (value === null || value === undefined || value === '') return false;

    const amount = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;

    // Nombre positif avec max 2 décimales
    return !isNaN(amount) && amount >= 0 && /^\d+(\.\d{1,2})?$/.test(amount.toString());
};

/**
 * Valide un pourcentage (0-100 avec max 2 décimales)
 * @param {string|number} value - Le pourcentage à valider
 * @returns {boolean}
 */
export const validatePercentage = (value) => {
    if (value === null || value === undefined || value === '') return false;

    const percent = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;

    return !isNaN(percent) && percent >= 0 && percent <= 100;
};

/**
 * Valide une date (format YYYY-MM-DD ou DD/MM/YYYY)
 * @param {string} value - La date à valider
 * @returns {boolean}
 */
export const validateDate = (value) => {
    if (!value) return false;

    // Format YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        const date = new Date(value);
        return !isNaN(date.getTime());
    }

    // Format DD/MM/YYYY
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
        const [day, month, year] = value.split('/');
        const date = new Date(year, month - 1, day);
        return !isNaN(date.getTime()) &&
               date.getDate() == day &&
               date.getMonth() == month - 1 &&
               date.getFullYear() == year;
    }

    return false;
};

/**
 * Valide un mot de passe fort
 * Au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre
 * @param {string} value - Le mot de passe à valider
 * @returns {boolean}
 */
export const validateStrongPassword = (value) => {
    if (!value) return false;

    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(value);
};

/**
 * Valide une couleur hexadécimale
 * @param {string} value - La couleur à valider (#RGB ou #RRGGBB)
 * @returns {boolean}
 */
export const validateHexColor = (value) => {
    if (!value) return false;

    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value);
};

// Export pour utilisation avec Vuelidate
export const ibanValidator = (value) => !value || validateIban(value);
export const bicValidator = (value) => !value || validateBic(value);
export const siretValidator = (value) => !value || validateSiret(value);
export const phoneFRValidator = (value) => !value || validatePhoneFR(value);
export const codePostalFRValidator = (value) => !value || validateCodePostalFR(value);
export const emailValidator = (value) => !value || validateEmail(value);
export const urlValidator = (value) => !value || validateUrl(value);
export const amountValidator = (value) => !value || validateAmount(value);
export const percentageValidator = (value) => !value || validatePercentage(value);
export const dateValidator = (value) => !value || validateDate(value);
export const strongPasswordValidator = (value) => !value || validateStrongPassword(value);
export const hexColorValidator = (value) => !value || validateHexColor(value);

// Messages d'erreur par défaut
export const validationMessages = {
    iban: 'L\'IBAN n\'est pas valide',
    bic: 'Le code BIC/SWIFT n\'est pas valide',
    siret: 'Le numéro SIRET n\'est pas valide',
    phoneFR: 'Le numéro de téléphone n\'est pas valide',
    codePostalFR: 'Le code postal n\'est pas valide',
    email: 'L\'adresse email n\'est pas valide',
    url: 'L\'URL n\'est pas valide',
    amount: 'Le montant n\'est pas valide',
    percentage: 'Le pourcentage doit être entre 0 et 100',
    date: 'La date n\'est pas valide',
    strongPassword: 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule et 1 chiffre',
    hexColor: 'La couleur doit être au format hexadécimal (#RGB ou #RRGGBB)',
};

export default {
    validateIban,
    validateBic,
    validateSiret,
    validatePhoneFR,
    validateCodePostalFR,
    validateEmail,
    validateUrl,
    validateAmount,
    validatePercentage,
    validateDate,
    validateStrongPassword,
    validateHexColor,
    ibanValidator,
    bicValidator,
    siretValidator,
    phoneFRValidator,
    codePostalFRValidator,
    emailValidator,
    urlValidator,
    amountValidator,
    percentageValidator,
    dateValidator,
    strongPasswordValidator,
    hexColorValidator,
    validationMessages,
};
