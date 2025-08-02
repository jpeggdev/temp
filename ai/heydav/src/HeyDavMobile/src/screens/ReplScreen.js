import React, { useState, useRef, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  ScrollView,
  TouchableOpacity,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { ApiService } from '../services/ApiService';

export default function ReplScreen() {
  const [command, setCommand] = useState('');
  const [history, setHistory] = useState([
    { type: 'system', text: 'Hey-Dav Mobile REPL v1.0' },
    { type: 'system', text: 'Type "help" for available commands.' },
  ]);
  const [isExecuting, setIsExecuting] = useState(false);
  const scrollViewRef = useRef();
  const inputRef = useRef();

  useEffect(() => {
    // Auto-focus input when screen loads
    if (inputRef.current) {
      inputRef.current.focus();
    }
  }, []);

  const executeCommand = async () => {
    if (!command.trim() || isExecuting) return;

    const currentCommand = command.trim();
    setCommand('');
    setIsExecuting(true);

    // Add command to history
    setHistory(prev => [...prev, { type: 'command', text: `dav> ${currentCommand}` }]);

    try {
      // Handle built-in commands
      if (currentCommand.toLowerCase() === 'help') {
        setHistory(prev => [...prev, { type: 'output', text: getHelpText() }]);
      } else if (currentCommand.toLowerCase() === 'clear') {
        setHistory([
          { type: 'system', text: 'Hey-Dav Mobile REPL v1.0' },
          { type: 'system', text: 'Type "help" for available commands.' },
        ]);
      } else {
        // Send command to backend
        const result = await ApiService.executeCommand(currentCommand);
        setHistory(prev => [...prev, { 
          type: result.success ? 'output' : 'error', 
          text: result.message || result.output || 'Command executed'
        }]);
      }
    } catch (error) {
      setHistory(prev => [...prev, { 
        type: 'error', 
        text: `Error: ${error.message || 'Failed to execute command'}`
      }]);
    } finally {
      setIsExecuting(false);
      // Auto-scroll to bottom
      setTimeout(() => {
        scrollViewRef.current?.scrollToEnd({ animated: true });
      }, 100);
    }
  };

  const getHelpText = () => {
    return `Available commands:
• help - Show this help message
• clear - Clear the screen
• status - Show current status
• todos - List all todos
• todo add "title" - Add a new todo
• goals - List goals (coming soon)
• exit - Close REPL

Examples:
dav> todo add "Buy groceries"
dav> todos
dav> status`;
  };

  const renderHistoryItem = (item, index) => {
    const getStyle = () => {
      switch (item.type) {
        case 'command': return styles.commandText;
        case 'output': return styles.outputText;
        case 'error': return styles.errorText;
        case 'system': return styles.systemText;
        default: return styles.outputText;
      }
    };

    return (
      <Text key={index} style={getStyle()} selectable>
        {item.text}
      </Text>
    );
  };

  const quickCommands = [
    { label: 'Status', command: 'status' },
    { label: 'List Tasks', command: 'todos' },
    { label: 'Help', command: 'help' },
    { label: 'Clear', command: 'clear' },
  ];

  const insertQuickCommand = (cmd) => {
    setCommand(cmd);
    inputRef.current?.focus();
  };

  return (
    <KeyboardAvoidingView 
      style={styles.container} 
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <View style={styles.header}>
        <View style={styles.headerContent}>
          <Ionicons name="terminal" size={24} color="#6366f1" />
          <Text style={styles.headerTitle}>Command Line Interface</Text>
        </View>
        <TouchableOpacity 
          style={styles.clearButton}
          onPress={() => setHistory([
            { type: 'system', text: 'Hey-Dav Mobile REPL v1.0' },
            { type: 'system', text: 'Type "help" for available commands.' },
          ])}
        >
          <Ionicons name="refresh" size={20} color="#6b7280" />
        </TouchableOpacity>
      </View>

      <ScrollView 
        ref={scrollViewRef}
        style={styles.terminal}
        contentContainerStyle={styles.terminalContent}
        showsVerticalScrollIndicator={false}
      >
        {history.map((item, index) => renderHistoryItem(item, index))}
        
        {isExecuting && (
          <View style={styles.executingIndicator}>
            <Text style={styles.executingText}>Executing...</Text>
          </View>
        )}
      </ScrollView>

      <View style={styles.quickCommandsContainer}>
        <ScrollView 
          horizontal 
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.quickCommands}
        >
          {quickCommands.map((cmd, index) => (
            <TouchableOpacity
              key={index}
              style={styles.quickCommandButton}
              onPress={() => insertQuickCommand(cmd.command)}
            >
              <Text style={styles.quickCommandText}>{cmd.label}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      <View style={styles.inputContainer}>
        <View style={styles.prompt}>
          <Text style={styles.promptText}>dav&gt;</Text>
        </View>
        <TextInput
          ref={inputRef}
          style={styles.input}
          value={command}
          onChangeText={setCommand}
          onSubmitEditing={executeCommand}
          placeholder="Enter command..."
          placeholderTextColor="#9ca3af"
          autoCapitalize="none"
          autoCorrect={false}
          returnKeyType="send"
          editable={!isExecuting}
        />
        <TouchableOpacity 
          style={[styles.executeButton, isExecuting && styles.executeButtonDisabled]}
          onPress={executeCommand}
          disabled={isExecuting || !command.trim()}
        >
          <Ionicons 
            name={isExecuting ? "hourglass" : "send"} 
            size={20} 
            color={isExecuting || !command.trim() ? "#9ca3af" : "#6366f1"} 
          />
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1f2937',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#111827',
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#f9fafb',
    marginLeft: 10,
  },
  clearButton: {
    padding: 8,
  },
  terminal: {
    flex: 1,
    backgroundColor: '#1f2937',
  },
  terminalContent: {
    padding: 15,
    paddingBottom: 20,
  },
  commandText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
    color: '#10b981',
    marginBottom: 5,
  },
  outputText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
    color: '#f9fafb',
    marginBottom: 5,
    marginLeft: 20,
  },
  errorText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
    color: '#ef4444',
    marginBottom: 5,
    marginLeft: 20,
  },
  systemText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 5,
    fontStyle: 'italic',
  },
  executingIndicator: {
    marginTop: 10,
  },
  executingText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
    color: '#f59e0b',
    fontStyle: 'italic',
  },
  quickCommandsContainer: {
    backgroundColor: '#374151',
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: '#4b5563',
  },
  quickCommands: {
    paddingHorizontal: 15,
  },
  quickCommandButton: {
    backgroundColor: '#4b5563',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    marginRight: 8,
  },
  quickCommandText: {
    fontSize: 12,
    color: '#f9fafb',
    fontWeight: '500',
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#111827',
    borderTopWidth: 1,
    borderTopColor: '#374151',
  },
  prompt: {
    marginRight: 10,
  },
  promptText: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 16,
    color: '#10b981',
    fontWeight: 'bold',
  },
  input: {
    flex: 1,
    height: 40,
    backgroundColor: '#374151',
    borderRadius: 8,
    paddingHorizontal: 12,
    color: '#f9fafb',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontSize: 14,
  },
  executeButton: {
    marginLeft: 10,
    padding: 10,
  },
  executeButtonDisabled: {
    opacity: 0.5,
  },
});