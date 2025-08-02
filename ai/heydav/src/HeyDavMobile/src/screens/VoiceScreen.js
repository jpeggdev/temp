import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as Speech from 'expo-speech';
import { ApiService } from '../services/ApiService';

export default function VoiceScreen() {
  const [isListening, setIsListening] = useState(false);
  const [isSpeaking, setIsSpeaking] = useState(false);
  const [lastCommand, setLastCommand] = useState('');
  const [lastResponse, setLastResponse] = useState('');
  const [voiceHistory, setVoiceHistory] = useState([]);

  useEffect(() => {
    // Initialize speech recognition if available
    return () => {
      // Cleanup speech recognition
    };
  }, []);

  const startListening = async () => {
    setIsListening(true);
    setLastCommand('');
    setLastResponse('');

    try {
      // For now, we'll simulate voice recognition
      // In a real implementation, you would use expo-speech-to-text or react-native-voice
      Alert.alert(
        'Voice Recognition',
        'Voice recognition would start here. For now, try the quick commands below.',
        [{ text: 'OK', onPress: () => setIsListening(false) }]
      );
    } catch (error) {
      console.error('Error starting voice recognition:', error);
      Alert.alert('Error', 'Failed to start voice recognition');
      setIsListening(false);
    }
  };

  const stopListening = () => {
    setIsListening(false);
  };

  const processVoiceCommand = async (command) => {
    try {
      setLastCommand(command);
      
      // Add to history
      setVoiceHistory(prev => [
        ...prev,
        { type: 'command', text: command, timestamp: new Date() }
      ]);

      // Process command through unified CommandOrchestrator API
      const result = await ApiService.executeCommand(command, 'mobile-voice');
      
      const response = result.message || 'Command processed successfully';
      setLastResponse(response);
      
      // Add response to history
      setVoiceHistory(prev => [
        ...prev,
        { type: 'response', text: response, timestamp: new Date(), success: result.success }
      ]);

      // Speak the response
      speakResponse(response);
      
    } catch (error) {
      console.error('Error processing voice command:', error);
      const errorMessage = 'Sorry, I couldn\'t process that command.';
      setLastResponse(errorMessage);
      setVoiceHistory(prev => [
        ...prev,
        { type: 'response', text: errorMessage, timestamp: new Date(), success: false }
      ]);
      speakResponse(errorMessage);
    }
  };

  const speakResponse = async (text) => {
    try {
      setIsSpeaking(true);
      await Speech.speak(text, {
        language: 'en-US',
        pitch: 1.0,
        rate: 0.8,
        onDone: () => setIsSpeaking(false),
        onError: () => setIsSpeaking(false),
      });
    } catch (error) {
      console.error('Error speaking response:', error);
      setIsSpeaking(false);
    }
  };

  const quickCommands = [
    { label: 'Show my tasks', command: 'show my tasks' },
    { label: 'Add new task', command: 'add task' },
    { label: 'Get status', command: 'status' },
    { label: 'What\'s my schedule', command: 'what\'s my schedule' },
    { label: 'Help', command: 'help' },
  ];

  const clearHistory = () => {
    setVoiceHistory([]);
    setLastCommand('');
    setLastResponse('');
  };

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={['#6366f1', '#8b5cf6']}
        style={styles.header}
      >
        <View style={styles.headerContent}>
          <Ionicons name="mic" size={32} color="white" />
          <Text style={styles.headerTitle}>Voice Commands</Text>
        </View>
        <Text style={styles.headerSubtitle}>
          Speak naturally to control Hey-Dav
        </Text>
      </LinearGradient>

      <View style={styles.voiceControlContainer}>
        <TouchableOpacity
          style={[
            styles.micButton,
            isListening && styles.micButtonActive,
            isSpeaking && styles.micButtonSpeaking
          ]}
          onPress={isListening ? stopListening : startListening}
          disabled={isSpeaking}
        >
          <LinearGradient
            colors={
              isSpeaking 
                ? ['#f59e0b', '#f97316']
                : isListening 
                  ? ['#ef4444', '#dc2626'] 
                  : ['#6366f1', '#8b5cf6']
            }
            style={styles.micButtonGradient}
          >
            <Ionicons 
              name={
                isSpeaking 
                  ? "volume-high" 
                  : isListening 
                    ? "stop" 
                    : "mic"
              } 
              size={48} 
              color="white" 
            />
          </LinearGradient>
        </TouchableOpacity>

        <Text style={styles.micButtonLabel}>
          {isSpeaking 
            ? 'Speaking...' 
            : isListening 
              ? 'Listening... Tap to stop' 
              : 'Tap to start listening'
          }
        </Text>
      </View>

      {lastCommand && (
        <View style={styles.lastInteraction}>
          <View style={styles.commandContainer}>
            <Ionicons name="person" size={20} color="#6366f1" />
            <Text style={styles.commandText}>"{lastCommand}"</Text>
          </View>
          {lastResponse && (
            <View style={styles.responseContainer}>
              <Ionicons name="chatbubble" size={20} color="#10b981" />
              <Text style={styles.responseText}>{lastResponse}</Text>
            </View>
          )}
        </View>
      )}

      <View style={styles.quickCommandsSection}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Quick Commands</Text>
          <Text style={styles.sectionSubtitle}>Tap to try these commands</Text>
        </View>
        
        <ScrollView style={styles.quickCommandsList}>
          {quickCommands.map((cmd, index) => (
            <TouchableOpacity
              key={index}
              style={styles.quickCommandButton}
              onPress={() => processVoiceCommand(cmd.command)}
            >
              <View style={styles.quickCommandContent}>
                <Ionicons name="mic-outline" size={20} color="#6366f1" />
                <Text style={styles.quickCommandText}>"{cmd.label}"</Text>
              </View>
              <Ionicons name="chevron-forward" size={20} color="#9ca3af" />
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {voiceHistory.length > 0 && (
        <View style={styles.historySection}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Commands</Text>
            <TouchableOpacity onPress={clearHistory}>
              <Text style={styles.clearText}>Clear</Text>
            </TouchableOpacity>
          </View>
          
          <ScrollView style={styles.historyList} showsVerticalScrollIndicator={false}>
            {voiceHistory.slice(-6).reverse().map((item, index) => (
              <View key={index} style={styles.historyItem}>
                <View style={styles.historyContent}>
                  <Ionicons 
                    name={item.type === 'command' ? 'person' : 'chatbubble'} 
                    size={16} 
                    color={item.type === 'command' ? '#6366f1' : '#10b981'} 
                  />
                  <Text style={[
                    styles.historyText,
                    item.type === 'command' ? styles.historyCommand : styles.historyResponse
                  ]}>
                    {item.text}
                  </Text>
                </View>
                <Text style={styles.historyTime}>
                  {item.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </Text>
              </View>
            ))}
          </ScrollView>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  header: {
    padding: 20,
    paddingBottom: 30,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: 'white',
    marginLeft: 12,
  },
  headerSubtitle: {
    fontSize: 16,
    color: 'rgba(255, 255, 255, 0.8)',
  },
  voiceControlContainer: {
    alignItems: 'center',
    paddingVertical: 40,
    backgroundColor: 'white',
    marginTop: -15,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
  },
  micButton: {
    width: 120,
    height: 120,
    borderRadius: 60,
    marginBottom: 20,
  },
  micButtonActive: {
    shadowColor: '#ef4444',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.5,
    shadowRadius: 20,
    elevation: 10,
  },
  micButtonSpeaking: {
    shadowColor: '#f59e0b',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.5,
    shadowRadius: 20,
    elevation: 10,
  },
  micButtonGradient: {
    width: '100%',
    height: '100%',
    borderRadius: 60,
    justifyContent: 'center',
    alignItems: 'center',
  },
  micButtonLabel: {
    fontSize: 16,
    color: '#6b7280',
    textAlign: 'center',
    fontWeight: '500',
  },
  lastInteraction: {
    padding: 20,
    backgroundColor: 'white',
  },
  commandContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  commandText: {
    fontSize: 16,
    color: '#1f2937',
    marginLeft: 10,
    flex: 1,
    fontStyle: 'italic',
  },
  responseContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  responseText: {
    fontSize: 16,
    color: '#1f2937',
    marginLeft: 10,
    flex: 1,
    lineHeight: 24,
  },
  quickCommandsSection: {
    flex: 1,
    padding: 20,
  },
  sectionHeader: {
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
  },
  sectionSubtitle: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 2,
  },
  quickCommandsList: {
    flex: 1,
  },
  quickCommandButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'white',
    padding: 16,
    borderRadius: 12,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  quickCommandContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  quickCommandText: {
    fontSize: 16,
    color: '#1f2937',
    marginLeft: 12,
    fontStyle: 'italic',
  },
  historySection: {
    backgroundColor: 'white',
    borderTopWidth: 1,
    borderTopColor: '#e5e7eb',
    maxHeight: 200,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    paddingBottom: 10,
  },
  clearText: {
    fontSize: 14,
    color: '#6366f1',
    fontWeight: '500',
  },
  historyList: {
    flex: 1,
    paddingHorizontal: 15,
  },
  historyItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  historyContent: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    flex: 1,
    marginRight: 10,
  },
  historyText: {
    fontSize: 14,
    marginLeft: 8,
    flex: 1,
    lineHeight: 20,
  },
  historyCommand: {
    color: '#6366f1',
    fontStyle: 'italic',
  },
  historyResponse: {
    color: '#1f2937',
  },
  historyTime: {
    fontSize: 12,
    color: '#9ca3af',
  },
});