import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Switch,
  TextInput,
  Alert,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function SettingsScreen() {
  const [settings, setSettings] = useState({
    serverUrl: 'http://localhost:5000',
    enableVoiceCommands: true,
    enableNotifications: true,
    enableEmailInterface: false,
    autoSync: true,
    syncInterval: 5, // minutes
    theme: 'system', // light, dark, system
    voiceLanguage: 'en-US',
  });
  
  const [isConnectionModalVisible, setIsConnectionModalVisible] = useState(false);
  const [tempServerUrl, setTempServerUrl] = useState('');

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      const savedSettings = await AsyncStorage.getItem('hey-dav-settings');
      if (savedSettings) {
        setSettings({ ...settings, ...JSON.parse(savedSettings) });
      }
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  };

  const saveSettings = async (newSettings) => {
    try {
      await AsyncStorage.setItem('hey-dav-settings', JSON.stringify(newSettings));
      setSettings(newSettings);
    } catch (error) {
      console.error('Error saving settings:', error);
      Alert.alert('Error', 'Failed to save settings');
    }
  };

  const updateSetting = (key, value) => {
    const newSettings = { ...settings, [key]: value };
    saveSettings(newSettings);
  };

  const testConnection = async () => {
    try {
      // Test connection to server
      const response = await fetch(`${settings.serverUrl}/api/health`);
      if (response.ok) {
        Alert.alert('Success', 'Connected to Hey-Dav server successfully!');
      } else {
        Alert.alert('Error', 'Server responded but health check failed');
      }
    } catch (error) {
      Alert.alert('Connection Failed', 'Could not connect to the server. Please check the URL and try again.');
    }
  };

  const showConnectionModal = () => {
    setTempServerUrl(settings.serverUrl);
    setIsConnectionModalVisible(true);
  };

  const saveServerUrl = () => {
    updateSetting('serverUrl', tempServerUrl);
    setIsConnectionModalVisible(false);
  };

  const resetSettings = () => {
    Alert.alert(
      'Reset Settings',
      'Are you sure you want to reset all settings to default?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Reset',
          style: 'destructive',
          onPress: () => {
            const defaultSettings = {
              serverUrl: 'http://localhost:5000',
              enableVoiceCommands: true,
              enableNotifications: true,
              enableEmailInterface: false,
              autoSync: true,
              syncInterval: 5,
              theme: 'system',
              voiceLanguage: 'en-US',
            };
            saveSettings(defaultSettings);
          },
        },
      ]
    );
  };

  const SettingSection = ({ title, children }) => (
    <View style={styles.section}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {children}
    </View>
  );

  const SettingRow = ({ icon, title, subtitle, onPress, rightComponent }) => (
    <TouchableOpacity style={styles.settingRow} onPress={onPress} disabled={!onPress}>
      <View style={styles.settingLeft}>
        <Ionicons name={icon} size={24} color="#6366f1" style={styles.settingIcon} />
        <View style={styles.settingText}>
          <Text style={styles.settingTitle}>{title}</Text>
          {subtitle && <Text style={styles.settingSubtitle}>{subtitle}</Text>}
        </View>
      </View>
      {rightComponent && <View style={styles.settingRight}>{rightComponent}</View>}
      {onPress && !rightComponent && (
        <Ionicons name="chevron-forward" size={20} color="#9ca3af" />
      )}
    </TouchableOpacity>
  );

  const SwitchRow = ({ icon, title, subtitle, value, onValueChange }) => (
    <SettingRow
      icon={icon}
      title={title}
      subtitle={subtitle}
      rightComponent={
        <Switch
          value={value}
          onValueChange={onValueChange}
          trackColor={{ false: '#d1d5db', true: '#c7d2fe' }}
          thumbColor={value ? '#6366f1' : '#f3f4f6'}
        />
      }
    />
  );

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Ionicons name="settings" size={32} color="#6366f1" />
        <Text style={styles.headerTitle}>Settings</Text>
      </View>

      <SettingSection title="Connection">
        <SettingRow
          icon="server"
          title="Server URL"
          subtitle={settings.serverUrl}
          onPress={showConnectionModal}
        />
        <SettingRow
          icon="wifi"
          title="Test Connection"
          subtitle="Check if Hey-Dav server is reachable"
          onPress={testConnection}
        />
        <SwitchRow
          icon="refresh"
          title="Auto Sync"
          subtitle="Automatically sync data with server"
          value={settings.autoSync}
          onValueChange={(value) => updateSetting('autoSync', value)}
        />
      </SettingSection>

      <SettingSection title="Interface">
        <SwitchRow
          icon="mic"
          title="Voice Commands"
          subtitle="Enable voice command recognition"
          value={settings.enableVoiceCommands}
          onValueChange={(value) => updateSetting('enableVoiceCommands', value)}
        />
        <SwitchRow
          icon="mail"
          title="Email Interface"
          subtitle="Process commands sent via email"
          value={settings.enableEmailInterface}
          onValueChange={(value) => updateSetting('enableEmailInterface', value)}
        />
        <SwitchRow
          icon="notifications"
          title="Push Notifications"
          subtitle="Receive notifications for tasks and reminders"
          value={settings.enableNotifications}
          onValueChange={(value) => updateSetting('enableNotifications', value)}
        />
      </SettingSection>

      <SettingSection title="Voice">
        <SettingRow
          icon="language"
          title="Voice Language"
          subtitle={settings.voiceLanguage}
          onPress={() => {
            Alert.alert('Language Selection', 'Language selection coming soon!');
          }}
        />
      </SettingSection>

      <SettingSection title="Data">
        <SettingRow
          icon="download"
          title="Export Data"
          subtitle="Download your tasks and settings"
          onPress={() => {
            Alert.alert('Export Data', 'Data export feature coming soon!');
          }}
        />
        <SettingRow
          icon="trash"
          title="Clear Cache"
          subtitle="Clear locally stored data"
          onPress={() => {
            Alert.alert('Clear Cache', 'Cache clearing feature coming soon!');
          }}
        />
      </SettingSection>

      <SettingSection title="About">
        <SettingRow
          icon="information-circle"
          title="Version"
          subtitle="Hey-Dav Mobile v1.0.0"
        />
        <SettingRow
          icon="help-circle"
          title="Help & Support"
          subtitle="Get help with using Hey-Dav"
          onPress={() => {
            Alert.alert('Help & Support', 'Support documentation coming soon!');
          }}
        />
        <SettingRow
          icon="refresh-circle"
          title="Reset Settings"
          subtitle="Reset all settings to default"
          onPress={resetSettings}
        />
      </SettingSection>

      <Modal
        animationType="slide"
        transparent={true}
        visible={isConnectionModalVisible}
        onRequestClose={() => setIsConnectionModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Server URL</Text>
              <TouchableOpacity onPress={() => setIsConnectionModalVisible(false)}>
                <Ionicons name="close" size={24} color="#6b7280" />
              </TouchableOpacity>
            </View>

            <Text style={styles.modalSubtitle}>
              Enter the URL of your Hey-Dav server:
            </Text>

            <TextInput
              style={styles.urlInput}
              placeholder="http://localhost:5000"
              value={tempServerUrl}
              onChangeText={setTempServerUrl}
              keyboardType="url"
              autoCapitalize="none"
              autoCorrect={false}
            />

            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={() => setIsConnectionModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.saveButton}
                onPress={saveServerUrl}
              >
                <Text style={styles.saveButtonText}>Save</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
    backgroundColor: 'white',
    borderBottomWidth: 1,
    borderBottomColor: '#e5e7eb',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1f2937',
    marginLeft: 12,
  },
  section: {
    backgroundColor: 'white',
    marginTop: 20,
    paddingVertical: 5,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6b7280',
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: '#f9fafb',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  settingLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  settingIcon: {
    marginRight: 15,
  },
  settingText: {
    flex: 1,
  },
  settingTitle: {
    fontSize: 16,
    fontWeight: '500',
    color: '#1f2937',
  },
  settingSubtitle: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 2,
  },
  settingRight: {
    marginLeft: 15,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: 'white',
    borderRadius: 16,
    padding: 20,
    width: '90%',
    maxWidth: 400,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
  },
  modalSubtitle: {
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 20,
  },
  urlInput: {
    borderWidth: 1,
    borderColor: '#d1d5db',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    marginBottom: 20,
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cancelButton: {
    flex: 1,
    paddingVertical: 12,
    marginRight: 8,
    borderRadius: 8,
    backgroundColor: '#f3f4f6',
    alignItems: 'center',
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '500',
    color: '#6b7280',
  },
  saveButton: {
    flex: 1,
    paddingVertical: 12,
    marginLeft: 8,
    borderRadius: 8,
    backgroundColor: '#6366f1',
    alignItems: 'center',
  },
  saveButtonText: {
    fontSize: 16,
    fontWeight: '500',
    color: 'white',
  },
});