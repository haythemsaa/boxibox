# 📱 Documentation API Mobile Boxibox

## 📋 Table des Matières
1. [Introduction](#introduction)
2. [Authentification](#authentification)
3. [Endpoints Disponibles](#endpoints-disponibles)
4. [Exemples de Requêtes](#exemples-de-requêtes)
5. [Codes d'Erreur](#codes-derreur)
6. [Intégration React Native](#intégration-react-native)

---

## 🎯 Introduction

L'API Mobile Boxibox permet aux clients de gérer leurs contrats, factures, paiements et de communiquer avec l'entreprise directement depuis leur application mobile.

**Base URL**: `https://votre-domaine.com/api/mobile/v1`

**Format des réponses**: JSON

**Authentification**: Laravel Sanctum (Bearer Token)

---

## 🔐 Authentification

### 1. Inscription

**POST** `/auth/register`

```json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@example.com",
  "telephone": "+33612345678",
  "password": "motdepasse123",
  "password_confirmation": "motdepasse123",
  "device_name": "iPhone 13 - iOS 15"
}
```

**Réponse (201)**:
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "client": {
      "id": 1,
      "nom": "Dupont",
      "prenom": "Jean",
      "email": "jean.dupont@example.com",
      "telephone": "+33612345678"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### 2. Connexion

**POST** `/auth/login`

```json
{
  "email": "jean.dupont@example.com",
  "password": "motdepasse123",
  "device_name": "iPhone 13 - iOS 15"
}
```

**Réponse (200)**:
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "client": {
      "id": 1,
      "nom": "Dupont",
      "prenom": "Jean",
      "email": "jean.dupont@example.com",
      "telephone": "+33612345678",
      "photo": null
    },
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### 3. Déconnexion

**POST** `/auth/logout`

**Headers**: `Authorization: Bearer {token}`

**Réponse (200)**:
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

### 4. Profil Utilisateur

**GET** `/auth/profile`

**Headers**: `Authorization: Bearer {token}`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@example.com",
    "telephone": "+33612345678",
    "adresse": "123 Rue de la Paix",
    "ville": "Paris",
    "code_postal": "75001",
    "pays": "France",
    "photo": null,
    "statut": "actif",
    "created_at": "01/01/2025"
  }
}
```

### 5. Mise à Jour Profil

**PUT** `/auth/profile`

**Headers**: `Authorization: Bearer {token}`

```json
{
  "nom": "Dupont",
  "prenom": "Jean-Pierre",
  "telephone": "+33612345679",
  "adresse": "456 Avenue des Champs",
  "ville": "Lyon",
  "code_postal": "69001"
}
```

### 6. Changer Mot de Passe

**POST** `/auth/change-password`

```json
{
  "current_password": "ancien_mot_de_passe",
  "new_password": "nouveau_mot_de_passe",
  "new_password_confirmation": "nouveau_mot_de_passe"
}
```

---

## 📊 Dashboard

### Dashboard Principal

**GET** `/dashboard`

**Headers**: `Authorization: Bearer {token}`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "client": {
      "nom_complet": "Jean Dupont",
      "email": "jean.dupont@example.com",
      "telephone": "+33612345678",
      "statut": "actif"
    },
    "statistiques": {
      "contrats_actifs": 2,
      "factures_impayees": 1,
      "montant_impaye": 150.00,
      "codes_acces_actifs": 2
    },
    "contrats": [
      {
        "id": 1,
        "numero": "CONT-2025-0001",
        "date_debut": "01/01/2025",
        "date_fin": null,
        "statut": "actif",
        "montant_mensuel": 75.00,
        "box": {
          "numero": "A-101",
          "statut": "occupe",
          "superficie": 5.5,
          "famille": "Box Standard",
          "emplacement": "Bâtiment A - Étage 1"
        }
      }
    ],
    "factures_impayees": [
      {
        "id": 1,
        "numero": "FACT-2025-0001",
        "date_emission": "01/01/2025",
        "date_echeance": "15/01/2025",
        "montant_total": 150.00,
        "statut": "impayee",
        "jours_retard": 5
      }
    ],
    "codes_acces": [
      {
        "id": 1,
        "type": "pin",
        "code_pin": "123456",
        "qr_code_url": null,
        "box_numero": "A-101",
        "date_fin_validite": "31/12/2025",
        "statut": "actif"
      }
    ]
  }
}
```

---

## 📄 Factures

### 1. Liste des Factures

**GET** `/factures?page=1`

**Headers**: `Authorization: Bearer {token}`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "factures": [
      {
        "id": 1,
        "numero": "FACT-2025-0001",
        "date_emission": "01/01/2025",
        "date_echeance": "15/01/2025",
        "montant_total": 150.00,
        "montant_paye": 0.00,
        "montant_restant": 150.00,
        "statut": "impayee",
        "statut_label": "Impayée",
        "jours_retard": 5,
        "pdf_url": "https://..."
      }
    ],
    "pagination": {
      "total": 45,
      "current_page": 1,
      "last_page": 3,
      "per_page": 20
    }
  }
}
```

### 2. Détail d'une Facture

**GET** `/factures/{id}`

**Headers**: `Authorization: Bearer {token}`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "numero": "FACT-2025-0001",
    "date_emission": "01/01/2025",
    "date_echeance": "15/01/2025",
    "montant_ht": 125.00,
    "montant_tva": 25.00,
    "montant_total": 150.00,
    "montant_paye": 0.00,
    "montant_restant": 150.00,
    "statut": "impayee",
    "statut_label": "Impayée",
    "notes": "Location Box A-101 - Janvier 2025",
    "contrat": {
      "numero": "CONT-2025-0001",
      "box_numero": "A-101"
    },
    "reglements": [],
    "pdf_url": "https://..."
  }
}
```

---

## 💳 Paiements

### 1. Créer une Intention de Paiement (Stripe)

**POST** `/payments/create-intent`

**Headers**: `Authorization: Bearer {token}`

```json
{
  "facture_id": 1,
  "amount": 150.00
}
```

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "client_secret": "pi_xxxxxxxxxxxx_secret_xxxxxxxxxxxx",
    "payment_intent_id": "pi_xxxxxxxxxxxx",
    "amount": 150.00,
    "currency": "eur"
  }
}
```

### 2. Confirmer un Paiement

**POST** `/payments/confirm`

```json
{
  "payment_intent_id": "pi_xxxxxxxxxxxx",
  "facture_id": 1
}
```

**Réponse (200)**:
```json
{
  "success": true,
  "message": "Paiement effectué avec succès",
  "data": {
    "payment_id": 123,
    "facture_id": 1,
    "montant": 150.00,
    "statut": "confirme"
  }
}
```

### 3. Historique des Paiements

**GET** `/payments/history`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "payments": [
      {
        "id": 123,
        "date": "20/01/2025",
        "facture_numero": "FACT-2025-0001",
        "montant": 150.00,
        "mode_paiement": "carte_bancaire",
        "statut": "confirme"
      }
    ]
  }
}
```

---

## 💬 Chat en Temps Réel

### 1. Envoyer un Message

**POST** `/chat/send`

```json
{
  "message": "Bonjour, j'ai une question sur ma facture"
}
```

**Réponse (201)**:
```json
{
  "success": true,
  "message": "Message envoyé",
  "data": {
    "id": 456,
    "message": "Bonjour, j'ai une question sur ma facture",
    "sent_by": "client",
    "read": false,
    "created_at": "2025-01-20 14:30:00"
  }
}
```

### 2. Récupérer l'Historique

**GET** `/chat/messages?page=1`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "messages": [
      {
        "id": 456,
        "message": "Bonjour, j'ai une question sur ma facture",
        "sent_by": "client",
        "read": true,
        "created_at": "2025-01-20 14:30:00"
      },
      {
        "id": 457,
        "message": "Bonjour, nous sommes là pour vous aider.",
        "sent_by": "admin",
        "read": true,
        "created_at": "2025-01-20 14:32:00"
      }
    ],
    "pagination": {
      "total": 15,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

### 3. Marquer comme Lu

**POST** `/chat/mark-read/{id}`

**Réponse (200)**:
```json
{
  "success": true,
  "message": "Message marqué comme lu"
}
```

---

## 🔔 Notifications

### 1. Liste des Notifications

**GET** `/notifications`

**Réponse (200)**:
```json
{
  "success": true,
  "data": {
    "total": 10,
    "non_lues": 3,
    "notifications": [
      {
        "id": "abc-123",
        "type": "App\\Notifications\\PaiementRecuNotification",
        "titre": "Paiement reçu",
        "message": "Votre paiement de 150€ a bien été reçu",
        "lu": false,
        "date": "20/01/2025 14:30"
      }
    ]
  }
}
```

### 2. Marquer Notification comme Lue

**POST** `/notifications/{id}/mark-read`

**Réponse (200)**:
```json
{
  "success": true,
  "message": "Notification marquée comme lue"
}
```

---

## ❌ Codes d'Erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Mauvaise requête |
| 401 | Non authentifié |
| 403 | Accès refusé |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 429 | Trop de requêtes (Rate Limiting) |
| 500 | Erreur serveur |

### Format d'Erreur

```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": {
    "email": ["Le champ email est requis."],
    "password": ["Le mot de passe doit contenir au moins 6 caractères."]
  }
}
```

---

## 📱 Intégration React Native

### Installation

```bash
npm install axios react-native-async-storage
```

### Configuration Axios

```javascript
// services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://votre-domaine.com/api/mobile/v1';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Intercepteur pour ajouter le token
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Déconnexion automatique
      await AsyncStorage.removeItem('auth_token');
      // Redirection vers login
    }
    return Promise.reject(error);
  }
);

export default api;
```

### Exemple d'Authentification

```javascript
// services/auth.js
import api from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const login = async (email, password) => {
  try {
    const response = await api.post('/auth/login', {
      email,
      password,
      device_name: 'React Native App',
    });

    if (response.data.success) {
      const { token, client } = response.data.data;
      await AsyncStorage.setItem('auth_token', token);
      await AsyncStorage.setItem('user', JSON.stringify(client));
      return { success: true, data: client };
    }
  } catch (error) {
    return {
      success: false,
      message: error.response?.data?.message || 'Erreur de connexion',
    };
  }
};

export const logout = async () => {
  try {
    await api.post('/auth/logout');
  } catch (error) {
    console.error('Erreur déconnexion:', error);
  } finally {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user');
  }
};
```

### Exemple d'Écran Dashboard

```javascript
// screens/DashboardScreen.js
import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, RefreshControl } from 'react-native';
import api from '../services/api';

export default function DashboardScreen() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const response = await api.get('/dashboard');
      if (response.data.success) {
        setData(response.data.data);
      }
    } catch (error) {
      console.error('Erreur:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboard();
  };

  if (loading) {
    return <Text>Chargement...</Text>;
  }

  return (
    <FlatList
      data={data?.contrats}
      keyExtractor={(item) => item.id.toString()}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
      renderItem={({ item }) => (
        <View>
          <Text>{item.numero}</Text>
          <Text>{item.box.numero}</Text>
          <Text>{item.montant_mensuel} €/mois</Text>
        </View>
      )}
    />
  );
}
```

---

## 🔒 Sécurité

### Bonnes Pratiques

1. **Stockage Sécurisé**: Utilisez `react-native-keychain` pour stocker le token
2. **HTTPS**: Toutes les requêtes doivent utiliser HTTPS
3. **Validation**: Validez toutes les entrées utilisateur
4. **Timeout**: Définissez des timeouts appropriés
5. **Rate Limiting**: Respectez les limites de l'API (60 req/min)

### Exemple avec Keychain

```javascript
import * as Keychain from 'react-native-keychain';

// Sauvegarder le token
await Keychain.setGenericPassword('auth_token', token);

// Récupérer le token
const credentials = await Keychain.getGenericPassword();
const token = credentials.password;

// Supprimer le token
await Keychain.resetGenericPassword();
```

---

## 📞 Support

Pour toute question sur l'API :
- **Email**: dev@boxibox.com
- **Documentation**: https://docs.boxibox.com
- **GitHub**: https://github.com/haythemsaa/boxibox

---

**Version**: 1.0.0
**Dernière mise à jour**: 06/10/2025
