import AsyncStorage from '@react-native-async-storage/async-storage';

class ApiService {
  constructor() {
    this.baseUrl = 'http://localhost:5000/api';
    this.initializeSettings();
  }

  async initializeSettings() {
    try {
      const settings = await AsyncStorage.getItem('hey-dav-settings');
      if (settings) {
        const { serverUrl } = JSON.parse(settings);
        if (serverUrl) {
          this.baseUrl = `${serverUrl}/api`;
        }
      }
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  }

  async request(endpoint, options = {}) {
    try {
      await this.initializeSettings(); // Ensure we have latest settings
      
      const url = `${this.baseUrl}${endpoint}`;
      const config = {
        headers: {
          'Content-Type': 'application/json',
          ...options.headers,
        },
        ...options,
      };

      const response = await fetch(url, config);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return await response.json();
      }
      
      return await response.text();
    } catch (error) {
      console.error('API request failed:', error);
      
      // Return mock data for development when server is not available
      if (error.message.includes('Network request failed') || error.message.includes('fetch')) {
        return this.getMockData(endpoint, options.method);
      }
      
      throw error;
    }
  }

  getMockData(endpoint, method) {
    // Mock data for development when backend is not available
    if (endpoint === '/todos' && method !== 'POST') {
      return [
        {
          id: '1',
          title: 'Complete mobile app development',
          description: 'Finish implementing all screens and API integration',
          priority: 'High',
          completed: false,
          createdAt: new Date().toISOString(),
          dueDate: new Date(Date.now() + 86400000).toISOString(), // Tomorrow
        },
        {
          id: '2',
          title: 'Write documentation',
          description: 'Document the API and mobile app usage',
          priority: 'Medium',
          completed: false,
          createdAt: new Date(Date.now() - 86400000).toISOString(), // Yesterday
        },
        {
          id: '3',
          title: 'Test voice commands',
          description: 'Ensure voice interface works correctly',
          priority: 'Low',
          completed: true,
          createdAt: new Date(Date.now() - 172800000).toISOString(), // 2 days ago
        },
      ];
    }

    if (endpoint === '/stats') {
      return {
        totalTasks: 3,
        completedTasks: 1,
        inProgressTasks: 0,
        pendingTasks: 2,
      };
    }

    if (endpoint.includes('/command') || endpoint.includes('/voice')) {
      return {
        success: true,
        message: 'Mock command processed successfully',
        data: null,
      };
    }

    if (method === 'POST') {
      return {
        success: true,
        id: Date.now().toString(),
        message: 'Resource created successfully',
      };
    }

    return { success: true, message: 'Mock response' };
  }

  // Task management
  async getTasks() {
    return this.request('/todos');
  }

  async createTask(task) {
    return this.request('/todos', {
      method: 'POST',
      body: JSON.stringify(task),
    });
  }

  async updateTask(taskId, updates) {
    return this.request(`/todos/${taskId}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  async deleteTask(taskId) {
    return this.request(`/todos/${taskId}`, {
      method: 'DELETE',
    });
  }

  async toggleTaskCompletion(taskId) {
    return this.request(`/todos/${taskId}/toggle`, {
      method: 'PATCH',
    });
  }

  // Statistics
  async getStats() {
    return this.request('/stats');
  }

  // Command execution
  async executeCommand(command, source = 'mobile') {
    return this.request('/command/execute', {
      method: 'POST',
      body: JSON.stringify({ 
        command: command,
        source: source,
        context: {
          timestamp: new Date().toISOString(),
          platform: 'mobile'
        }
      }),
    });
  }

  // Voice commands
  async startVoiceCommands() {
    return this.request('/voice/start', {
      method: 'POST',
    });
  }

  async stopVoiceCommands() {
    return this.request('/voice/stop', {
      method: 'POST',
    });
  }

  async getVoiceStatus() {
    return this.request('/voice/status');
  }

  // Goals (placeholder for future implementation)
  async getGoals() {
    return this.request('/goals');
  }

  async createGoal(goal) {
    return this.request('/goals', {
      method: 'POST',
      body: JSON.stringify(goal),
    });
  }

  // System health and status
  async checkHealth() {
    return this.request('/system/health');
  }

  async getSystemStatus() {
    return this.request('/system/status');
  }

  async restartServices(options = {}) {
    return this.request('/system/restart-services', {
      method: 'POST',
      body: JSON.stringify(options),
    });
  }

  // Agent management
  async getAgents() {
    return this.request('/agent');
  }

  async createAgent(agent) {
    return this.request('/agent', {
      method: 'POST',
      body: JSON.stringify(agent),
    });
  }

  async getAgentTasks() {
    return this.request('/agent/tasks');
  }

  async createAgentTask(task) {
    return this.request('/agent/tasks', {
      method: 'POST',
      body: JSON.stringify(task),
    });
  }

  async processAgentTasks() {
    return this.request('/agent/process-pending-tasks', {
      method: 'POST',
    });
  }

  // Email interface
  async sendEmail(to, subject, body) {
    return this.request('/email/send', {
      method: 'POST',
      body: JSON.stringify({ to, subject, body }),
    });
  }

  // Settings sync
  async syncSettings(settings) {
    return this.request('/settings/sync', {
      method: 'POST',
      body: JSON.stringify(settings),
    });
  }

  async getServerSettings() {
    return this.request('/settings');
  }
}

export const ApiService = new ApiService();
export default ApiService;