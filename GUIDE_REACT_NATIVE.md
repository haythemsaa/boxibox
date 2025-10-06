# Guide de Démarrage React Native - Boxibox Mobile

## Table des Matières

1. [Prérequis](#prérequis)
2. [Installation du Projet](#installation-du-projet)
3. [Structure du Projet](#structure-du-projet)
4. [Configuration](#configuration)
5. [Services API](#services-api)
6. [Écrans Principaux](#écrans-principaux)
7. [Navigation](#navigation)
8. [Gestion d'État](#gestion-détat)
9. [Composants Réutilisables](#composants-réutilisables)
10. [Tests](#tests)
11. [Déploiement](#déploiement)

---

## Prérequis

### Environnement de Développement

- **Node.js** >= 16.x
- **npm** ou **yarn**
- **React Native CLI** ou **Expo CLI**
- **Android Studio** (pour Android)
- **Xcode** (pour iOS, macOS uniquement)

### Installation de React Native CLI

```bash
npm install -g react-native-cli
# ou avec Expo (recommandé pour débuter)
npm install -g expo-cli
```

---

## Installation du Projet

### Option 1: Avec Expo (Recommandé)

```bash
# Créer un nouveau projet Expo
npx create-expo-app boxibox-mobile
cd boxibox-mobile

# Installer les dépendances essentielles
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context
npm install axios
npm install @react-native-async-storage/async-storage
npm install react-native-gesture-handler react-native-reanimated
npm install @stripe/stripe-react-native
npm install react-native-document-picker
npm install react-native-image-picker
npm install react-native-pdf
npm install date-fns
```

### Option 2: Avec React Native CLI

```bash
npx react-native init BoxiboxMobile
cd BoxiboxMobile

# Installer les mêmes dépendances qu'avec Expo
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context
npm install axios
npm install @react-native-async-storage/async-storage
npm install react-native-gesture-handler react-native-reanimated
npm install @stripe/stripe-react-native
npm install react-native-document-picker
npm install react-native-image-picker
npm install react-native-pdf
npm install date-fns

# Installer les pods iOS (macOS uniquement)
cd ios && pod install && cd ..
```

---

## Structure du Projet

```
boxibox-mobile/
├── src/
│   ├── api/
│   │   ├── client.js                 # Configuration Axios
│   │   ├── auth.js                   # API d'authentification
│   │   ├── dashboard.js              # API du tableau de bord
│   │   ├── factures.js               # API des factures
│   │   ├── contrats.js               # API des contrats
│   │   ├── payments.js               # API des paiements
│   │   └── chat.js                   # API du chat
│   ├── components/
│   │   ├── common/
│   │   │   ├── Button.js
│   │   │   ├── Input.js
│   │   │   ├── Card.js
│   │   │   ├── LoadingSpinner.js
│   │   │   └── ErrorMessage.js
│   │   ├── factures/
│   │   │   ├── FactureCard.js
│   │   │   └── FactureListItem.js
│   │   ├── contrats/
│   │   │   └── ContratCard.js
│   │   └── chat/
│   │       ├── MessageBubble.js
│   │       └── MessageInput.js
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── LoginScreen.js
│   │   │   ├── RegisterScreen.js
│   │   │   └── ForgotPasswordScreen.js
│   │   ├── main/
│   │   │   ├── DashboardScreen.js
│   │   │   ├── FacturesScreen.js
│   │   │   ├── FactureDetailScreen.js
│   │   │   ├── ContratsScreen.js
│   │   │   ├── ContratDetailScreen.js
│   │   │   ├── PaymentScreen.js
│   │   │   ├── ChatScreen.js
│   │   │   └── ProfileScreen.js
│   ├── navigation/
│   │   ├── AppNavigator.js
│   │   ├── AuthNavigator.js
│   │   └── MainNavigator.js
│   ├── context/
│   │   ├── AuthContext.js
│   │   └── ThemeContext.js
│   ├── utils/
│   │   ├── storage.js
│   │   ├── formatters.js
│   │   └── validators.js
│   ├── constants/
│   │   ├── colors.js
│   │   └── config.js
│   └── App.js
├── assets/
├── package.json
└── README.md
```

---

## Configuration

### 1. Configuration de l'API (`src/constants/config.js`)

```javascript
export const API_CONFIG = {
  BASE_URL: 'http://votre-domaine.com/api/mobile/v1',
  // Pour le développement local:
  // BASE_URL: 'http://10.0.2.2:8000/api/mobile/v1', // Android Emulator
  // BASE_URL: 'http://localhost:8000/api/mobile/v1', // iOS Simulator
  TIMEOUT: 30000,
  STRIPE_PUBLISHABLE_KEY: 'pk_test_VOTRE_CLE_STRIPE',
};

export const STORAGE_KEYS = {
  AUTH_TOKEN: '@boxibox_auth_token',
  USER_DATA: '@boxibox_user_data',
};
```

### 2. Configuration Axios (`src/api/client.js`)

```javascript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_CONFIG, STORAGE_KEYS } from '../constants/config';

const apiClient = axios.create({
  baseURL: API_CONFIG.BASE_URL,
  timeout: API_CONFIG.TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Intercepteur pour ajouter le token
apiClient.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
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
apiClient.interceptors.response.use(
  (response) => response.data,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expiré, déconnecter l'utilisateur
      await AsyncStorage.multiRemove([STORAGE_KEYS.AUTH_TOKEN, STORAGE_KEYS.USER_DATA]);
      // Rediriger vers l'écran de login
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```

---

## Services API

### Auth Service (`src/api/auth.js`)

```javascript
import apiClient from './client';

export const authAPI = {
  login: async (email, password, deviceName) => {
    return await apiClient.post('/auth/login', {
      email,
      password,
      device_name: deviceName,
    });
  },

  register: async (userData) => {
    return await apiClient.post('/auth/register', userData);
  },

  logout: async () => {
    return await apiClient.post('/auth/logout');
  },

  getProfile: async () => {
    return await apiClient.get('/auth/profile');
  },

  updateProfile: async (data) => {
    return await apiClient.put('/auth/profile', data);
  },

  changePassword: async (currentPassword, newPassword) => {
    return await apiClient.post('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword,
    });
  },

  forgotPassword: async (email) => {
    return await apiClient.post('/auth/forgot-password', { email });
  },
};
```

### Dashboard Service (`src/api/dashboard.js`)

```javascript
import apiClient from './client';

export const dashboardAPI = {
  getDashboard: async () => {
    return await apiClient.get('/dashboard');
  },

  getNotifications: async () => {
    return await apiClient.get('/notifications');
  },

  markNotificationAsRead: async (id) => {
    return await apiClient.post(`/notifications/${id}/mark-read`);
  },
};
```

### Factures Service (`src/api/factures.js`)

```javascript
import apiClient from './client';

export const facturesAPI = {
  getFactures: async (page = 1) => {
    return await apiClient.get('/factures', { params: { page } });
  },

  getFacture: async (id) => {
    return await apiClient.get(`/factures/${id}`);
  },

  downloadPDF: async (id) => {
    return await apiClient.get(`/factures/${id}/pdf`, {
      responseType: 'blob',
    });
  },
};
```

### Payments Service (`src/api/payments.js`)

```javascript
import apiClient from './client';

export const paymentsAPI = {
  createPaymentIntent: async (factureId) => {
    return await apiClient.post('/payments/create-intent', {
      facture_id: factureId,
    });
  },

  confirmPayment: async (paymentIntentId, factureId) => {
    return await apiClient.post('/payments/confirm', {
      payment_intent_id: paymentIntentId,
      facture_id: factureId,
    });
  },

  getPaymentHistory: async (page = 1) => {
    return await apiClient.get('/payments/history', { params: { page } });
  },

  getPaymentMethods: async () => {
    return await apiClient.get('/payments/methods');
  },
};
```

### Chat Service (`src/api/chat.js`)

```javascript
import apiClient from './client';

export const chatAPI = {
  sendMessage: async (message, attachment = null) => {
    const formData = new FormData();
    formData.append('message', message);

    if (attachment) {
      formData.append('attachment', {
        uri: attachment.uri,
        type: attachment.type,
        name: attachment.name,
      });
    }

    return await apiClient.post('/chat/send', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  getMessages: async (page = 1) => {
    return await apiClient.get('/chat/messages', { params: { page } });
  },

  markAsRead: async (id) => {
    return await apiClient.post(`/chat/mark-read/${id}`);
  },

  getUnreadCount: async () => {
    return await apiClient.get('/chat/unread-count');
  },
};
```

---

## Écrans Principaux

### Login Screen (`src/screens/auth/LoginScreen.js`)

```javascript
import React, { useState, useContext } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../../context/AuthContext';
import { authAPI } from '../../api/auth';
import { STORAGE_KEYS } from '../../constants/config';
import * as Device from 'expo-device';

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useContext(AuthContext);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    setLoading(true);
    try {
      const deviceName = `${Device.brand} ${Device.modelName}`;
      const response = await authAPI.login(email, password, deviceName);

      if (response.success) {
        const { client, token } = response.data;

        // Sauvegarder le token et les données utilisateur
        await AsyncStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
        await AsyncStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(client));

        // Mettre à jour le contexte
        login(client, token);
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Erreur de connexion';
      Alert.alert('Erreur', message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Boxibox</Text>
      <Text style={styles.subtitle}>Espace Client</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
      />

      <TextInput
        style={styles.input}
        placeholder="Mot de passe"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />

      <TouchableOpacity
        style={styles.button}
        onPress={handleLogin}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.buttonText}>Se connecter</Text>
        )}
      </TouchableOpacity>

      <TouchableOpacity onPress={() => navigation.navigate('ForgotPassword')}>
        <Text style={styles.linkText}>Mot de passe oublié ?</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: '#f5f5f5',
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 10,
    color: '#333',
  },
  subtitle: {
    fontSize: 18,
    textAlign: 'center',
    marginBottom: 40,
    color: '#666',
  },
  input: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
    fontSize: 16,
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  linkText: {
    color: '#007AFF',
    textAlign: 'center',
    marginTop: 20,
  },
});
```

### Dashboard Screen (`src/screens/main/DashboardScreen.js`)

```javascript
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  RefreshControl,
  TouchableOpacity,
} from 'react-native';
import { dashboardAPI } from '../../api/dashboard';

export default function DashboardScreen({ navigation }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const response = await dashboardAPI.getDashboard();
      if (response.success) {
        setData(response.data);
      }
    } catch (error) {
      console.error('Erreur dashboard:', error);
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
    return (
      <View style={styles.centered}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      {/* Statistiques */}
      <View style={styles.statsContainer}>
        <View style={styles.statCard}>
          <Text style={styles.statValue}>{data.statistiques.contrats_actifs}</Text>
          <Text style={styles.statLabel}>Contrats Actifs</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statValue}>{data.statistiques.factures_impayees}</Text>
          <Text style={styles.statLabel}>Factures Impayées</Text>
        </View>
      </View>

      {/* Montant impayé */}
      {data.statistiques.montant_impaye > 0 && (
        <View style={styles.alertCard}>
          <Text style={styles.alertTitle}>Montant à payer</Text>
          <Text style={styles.alertAmount}>{data.statistiques.montant_impaye.toFixed(2)} €</Text>
          <TouchableOpacity
            style={styles.payButton}
            onPress={() => navigation.navigate('Factures')}
          >
            <Text style={styles.payButtonText}>Payer maintenant</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Contrats actifs */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Mes Contrats</Text>
        {data.contrats.map((contrat) => (
          <TouchableOpacity
            key={contrat.id}
            style={styles.card}
            onPress={() => navigation.navigate('ContratDetail', { id: contrat.id })}
          >
            <Text style={styles.cardTitle}>Box {contrat.box.numero}</Text>
            <Text style={styles.cardSubtitle}>{contrat.box.emplacement}</Text>
            <Text style={styles.cardPrice}>{contrat.montant_loyer.toFixed(2)} € / mois</Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Codes d'accès */}
      {data.codes_acces.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Codes d'Accès</Text>
          {data.codes_acces.map((code) => (
            <View key={code.id} style={styles.codeCard}>
              <Text style={styles.codeType}>{code.type_label}</Text>
              <Text style={styles.codeValue}>{code.code}</Text>
              <Text style={styles.codeExpiry}>
                {code.date_fin_validite ? `Expire le ${code.date_fin_validite}` : 'Permanent'}
              </Text>
            </View>
          ))}
        </View>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statsContainer: {
    flexDirection: 'row',
    padding: 15,
    gap: 15,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 20,
    borderRadius: 10,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#007AFF',
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
    textAlign: 'center',
  },
  alertCard: {
    backgroundColor: '#FFF3CD',
    padding: 20,
    margin: 15,
    borderRadius: 10,
    borderLeftWidth: 4,
    borderLeftColor: '#FFC107',
  },
  alertTitle: {
    fontSize: 14,
    color: '#856404',
    marginBottom: 5,
  },
  alertAmount: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#856404',
    marginBottom: 15,
  },
  payButton: {
    backgroundColor: '#007AFF',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  payButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  section: {
    padding: 15,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 15,
    color: '#333',
  },
  card: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  cardSubtitle: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  cardPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#007AFF',
    marginTop: 10,
  },
  codeCard: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
  },
  codeType: {
    fontSize: 12,
    color: '#666',
    textTransform: 'uppercase',
  },
  codeValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#007AFF',
    marginVertical: 10,
    letterSpacing: 2,
  },
  codeExpiry: {
    fontSize: 12,
    color: '#999',
  },
});
```

### Chat Screen (`src/screens/main/ChatScreen.js`)

```javascript
import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  FlatList,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { chatAPI } from '../../api/chat';
import DocumentPicker from 'react-native-document-picker';

export default function ChatScreen() {
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [loading, setLoading] = useState(false);
  const [attachment, setAttachment] = useState(null);
  const flatListRef = useRef();

  const fetchMessages = async () => {
    try {
      const response = await chatAPI.getMessages();
      if (response.success) {
        setMessages(response.data.messages);
      }
    } catch (error) {
      console.error('Erreur messages:', error);
    }
  };

  useEffect(() => {
    fetchMessages();
    // TODO: Configurer WebSocket pour les messages en temps réel
  }, []);

  const handleSend = async () => {
    if (!inputText.trim() && !attachment) return;

    setLoading(true);
    try {
      const response = await chatAPI.sendMessage(inputText, attachment);
      if (response.success) {
        setMessages([response.data, ...messages]);
        setInputText('');
        setAttachment(null);
      }
    } catch (error) {
      console.error('Erreur envoi:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAttachment = async () => {
    try {
      const result = await DocumentPicker.pick({
        type: [DocumentPicker.types.images, DocumentPicker.types.pdf],
      });
      setAttachment(result[0]);
    } catch (error) {
      if (!DocumentPicker.isCancel(error)) {
        console.error('Erreur sélection fichier:', error);
      }
    }
  };

  const renderMessage = ({ item }) => {
    const isClient = item.sent_by === 'client';
    return (
      <View
        style={[
          styles.messageBubble,
          isClient ? styles.clientMessage : styles.adminMessage,
        ]}
      >
        <Text style={styles.messageText}>{item.message}</Text>
        {item.attachment_url && (
          <Text style={styles.attachmentText}>📎 Pièce jointe</Text>
        )}
        <Text style={styles.messageTime}>{item.formatted_time}</Text>
      </View>
    );
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={90}
    >
      <FlatList
        ref={flatListRef}
        data={messages}
        renderItem={renderMessage}
        keyExtractor={(item) => item.id.toString()}
        inverted
        style={styles.messageList}
      />

      <View style={styles.inputContainer}>
        {attachment && (
          <View style={styles.attachmentPreview}>
            <Text style={styles.attachmentName}>{attachment.name}</Text>
            <TouchableOpacity onPress={() => setAttachment(null)}>
              <Text style={styles.removeAttachment}>✕</Text>
            </TouchableOpacity>
          </View>
        )}

        <View style={styles.inputRow}>
          <TouchableOpacity style={styles.attachButton} onPress={handleAttachment}>
            <Text style={styles.attachButtonText}>📎</Text>
          </TouchableOpacity>

          <TextInput
            style={styles.input}
            placeholder="Votre message..."
            value={inputText}
            onChangeText={setInputText}
            multiline
          />

          <TouchableOpacity
            style={styles.sendButton}
            onPress={handleSend}
            disabled={loading || (!inputText.trim() && !attachment)}
          >
            <Text style={styles.sendButtonText}>Envoyer</Text>
          </TouchableOpacity>
        </View>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  messageList: {
    flex: 1,
    padding: 10,
  },
  messageBubble: {
    maxWidth: '75%',
    padding: 10,
    borderRadius: 15,
    marginBottom: 10,
  },
  clientMessage: {
    alignSelf: 'flex-end',
    backgroundColor: '#007AFF',
  },
  adminMessage: {
    alignSelf: 'flex-start',
    backgroundColor: '#E5E5EA',
  },
  messageText: {
    fontSize: 16,
    color: '#000',
  },
  attachmentText: {
    fontSize: 12,
    marginTop: 5,
    fontStyle: 'italic',
  },
  messageTime: {
    fontSize: 10,
    color: '#666',
    marginTop: 5,
    textAlign: 'right',
  },
  inputContainer: {
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#ddd',
    padding: 10,
  },
  attachmentPreview: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: '#f0f0f0',
    padding: 10,
    borderRadius: 8,
    marginBottom: 10,
  },
  attachmentName: {
    flex: 1,
    fontSize: 14,
  },
  removeAttachment: {
    color: '#FF3B30',
    fontWeight: 'bold',
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  attachButton: {
    padding: 10,
  },
  attachButtonText: {
    fontSize: 24,
  },
  input: {
    flex: 1,
    backgroundColor: '#f0f0f0',
    borderRadius: 20,
    paddingHorizontal: 15,
    paddingVertical: 10,
    marginHorizontal: 10,
    maxHeight: 100,
  },
  sendButton: {
    backgroundColor: '#007AFF',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
  },
  sendButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});
```

---

## Navigation

### App Navigator (`src/navigation/AppNavigator.js`)

```javascript
import React, { useContext } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { AuthContext } from '../context/AuthContext';
import AuthNavigator from './AuthNavigator';
import MainNavigator from './MainNavigator';

export default function AppNavigator() {
  const { user } = useContext(AuthContext);

  return (
    <NavigationContainer>
      {user ? <MainNavigator /> : <AuthNavigator />}
    </NavigationContainer>
  );
}
```

### Main Navigator (`src/navigation/MainNavigator.js`)

```javascript
import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createStackNavigator } from '@react-navigation/stack';
import DashboardScreen from '../screens/main/DashboardScreen';
import FacturesScreen from '../screens/main/FacturesScreen';
import FactureDetailScreen from '../screens/main/FactureDetailScreen';
import ContratsScreen from '../screens/main/ContratsScreen';
import ContratDetailScreen from '../screens/main/ContratDetailScreen';
import ChatScreen from '../screens/main/ChatScreen';
import ProfileScreen from '../screens/main/ProfileScreen';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

function FacturesStack() {
  return (
    <Stack.Navigator>
      <Stack.Screen name="FacturesList" component={FacturesScreen} options={{ title: 'Factures' }} />
      <Stack.Screen name="FactureDetail" component={FactureDetailScreen} options={{ title: 'Détail Facture' }} />
    </Stack.Navigator>
  );
}

function ContratsStack() {
  return (
    <Stack.Navigator>
      <Stack.Screen name="ContratsList" component={ContratsScreen} options={{ title: 'Contrats' }} />
      <Stack.Screen name="ContratDetail" component={ContratDetailScreen} options={{ title: 'Détail Contrat' }} />
    </Stack.Navigator>
  );
}

export default function MainNavigator() {
  return (
    <Tab.Navigator>
      <Tab.Screen
        name="Dashboard"
        component={DashboardScreen}
        options={{
          title: 'Accueil',
          tabBarIcon: () => '🏠'
        }}
      />
      <Tab.Screen
        name="Factures"
        component={FacturesStack}
        options={{
          title: 'Factures',
          tabBarIcon: () => '📄',
          headerShown: false
        }}
      />
      <Tab.Screen
        name="Contrats"
        component={ContratsStack}
        options={{
          title: 'Contrats',
          tabBarIcon: () => '📋',
          headerShown: false
        }}
      />
      <Tab.Screen
        name="Chat"
        component={ChatScreen}
        options={{
          title: 'Support',
          tabBarIcon: () => '💬'
        }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileScreen}
        options={{
          title: 'Profil',
          tabBarIcon: () => '👤'
        }}
      />
    </Tab.Navigator>
  );
}
```

---

## Gestion d'État

### Auth Context (`src/context/AuthContext.js`)

```javascript
import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS } from '../constants/config';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStoredAuth();
  }, []);

  const loadStoredAuth = async () => {
    try {
      const storedToken = await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
      const storedUser = await AsyncStorage.getItem(STORAGE_KEYS.USER_DATA);

      if (storedToken && storedUser) {
        setToken(storedToken);
        setUser(JSON.parse(storedUser));
      }
    } catch (error) {
      console.error('Erreur chargement auth:', error);
    } finally {
      setLoading(false);
    }
  };

  const login = (userData, authToken) => {
    setUser(userData);
    setToken(authToken);
  };

  const logout = async () => {
    await AsyncStorage.multiRemove([STORAGE_KEYS.AUTH_TOKEN, STORAGE_KEYS.USER_DATA]);
    setUser(null);
    setToken(null);
  };

  return (
    <AuthContext.Provider value={{ user, token, login, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
};
```

---

## Tests

### Lancer les Tests

```bash
npm test
```

### Exemple de Test (`__tests__/api/auth.test.js`)

```javascript
import { authAPI } from '../../src/api/auth';

describe('Auth API', () => {
  it('should login successfully', async () => {
    const response = await authAPI.login('test@example.com', 'password123', 'iPhone');
    expect(response.success).toBe(true);
    expect(response.data.token).toBeDefined();
  });
});
```

---

## Déploiement

### Build Android

```bash
cd android
./gradlew assembleRelease
# APK généré dans: android/app/build/outputs/apk/release/
```

### Build iOS

```bash
cd ios
pod install
# Ouvrir le workspace dans Xcode et archiver
```

### Avec Expo

```bash
# Build Android
expo build:android

# Build iOS
expo build:ios
```

---

## Ressources

- [Documentation React Native](https://reactnative.dev/)
- [Documentation Expo](https://docs.expo.dev/)
- [React Navigation](https://reactnavigation.org/)
- [Stripe React Native](https://stripe.com/docs/mobile/react-native)
- [Documentation API Boxibox](./API_MOBILE_DOCUMENTATION.md)

---

## Support

Pour toute question technique ou bug, contactez l'équipe de développement.
