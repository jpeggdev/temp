using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Services;

public class AgentOrchestrator(
    IAgentRepository agentRepository,
    IAgentTaskRepository taskRepository,
    IMapServerRepository mcpServerRepository,
    IMcpClient mcpClient,
    ILogger<AgentOrchestrator> logger)
    : IAgentOrchestrator
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentTaskRepository _taskRepository = taskRepository ?? throw new ArgumentNullException(nameof(taskRepository));
    private readonly IMapServerRepository _mcpServerRepository = mcpServerRepository ?? throw new ArgumentNullException(nameof(mcpServerRepository));
    private readonly IMcpClient _mcpClient = mcpClient ?? throw new ArgumentNullException(nameof(mcpClient));
    private readonly ILogger<AgentOrchestrator> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    public async Task<Guid?> AssignTaskAsync(AgentTask task, CancellationToken cancellationToken = default)
    {
        var bestAgent = await FindBestAgentAsync(task, cancellationToken);
        
        if (bestAgent == null)
        {
            _logger.LogWarning("No suitable agent found for task {TaskId}: {TaskTitle}", task.Id, task.Title);
            return null;
        }

        try
        {
            bestAgent.AssignTask(task);
            await _agentRepository.UpdateAsync(bestAgent, cancellationToken);
            
            _logger.LogInformation("Task {TaskId} assigned to agent {AgentId} ({AgentName})", 
                task.Id, bestAgent.Id, bestAgent.Name);
            
            return bestAgent.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to assign task {TaskId} to agent {AgentId}", task.Id, bestAgent.Id);
            return null;
        }
    }

    public async Task<AIAgent?> FindBestAgentAsync(AgentTask task, CancellationToken cancellationToken = default)
    {
        // First, try to find the best agent using repository logic
        var bestAgent = await _agentRepository.GetBestAgentForTaskAsync(task, cancellationToken);
        
        if (bestAgent != null)
        {
            return bestAgent;
        }

        // If no perfect match, find agents with some capabilities
        var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
        
        // Score agents based on capability match and current load
        var scoredAgents = availableAgents.Select(agent => new
        {
            Agent = agent,
            Score = CalculateAgentScore(agent, task)
        })
        .Where(x => x.Score > 0)
        .OrderByDescending(x => x.Score)
        .ToList();

        return scoredAgents.FirstOrDefault()?.Agent;
    }

    public async Task<bool> ExecuteTaskAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        var task = await _taskRepository.GetByIdAsync(taskId, cancellationToken);
        if (task == null)
        {
            _logger.LogWarning("Task {TaskId} not found", taskId);
            return false;
        }

        if (task.AssignedAgentId == null)
        {
            _logger.LogWarning("Task {TaskId} has no assigned agent", taskId);
            return false;
        }

        var agent = await _agentRepository.GetByIdAsync(task.AssignedAgentId.Value, cancellationToken);
        if (agent == null)
        {
            _logger.LogWarning("Agent {AgentId} not found for task {TaskId}", task.AssignedAgentId, taskId);
            return false;
        }

        try
        {
            task.Start();
            var startTime = DateTime.UtcNow;

            _logger.LogInformation("Starting execution of task {TaskId} by agent {AgentId}", taskId, agent.Id);

            // Simulate task execution - in real implementation, this would involve
            // calling the agent's AI model or executing specific task logic
            await Task.Delay(1000, cancellationToken); // Placeholder for actual work

            var endTime = DateTime.UtcNow;
            var executionTime = endTime - startTime;

            // Mark task as completed
            task.Complete($"Task completed successfully by {agent.Name}");
            agent.CompleteTask(taskId, executionTime);

            await _taskRepository.UpdateAsync(task, cancellationToken);
            await _agentRepository.UpdateAsync(agent, cancellationToken);

            _logger.LogInformation("Task {TaskId} completed successfully in {ExecutionTime}ms", 
                taskId, executionTime.TotalMilliseconds);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute task {TaskId}", taskId);
            
            task.Fail(ex.Message);
            agent.FailTask(taskId, ex.Message);
            
            await _taskRepository.UpdateAsync(task, cancellationToken);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            return false;
        }
    }

    public async Task<IEnumerable<AgentTask>> GetPendingTasksAsync(CancellationToken cancellationToken = default)
    {
        return await _taskRepository.GetPendingTasksAsync(cancellationToken);
    }

    public async Task ProcessPendingTasksAsync(CancellationToken cancellationToken = default)
    {
        var pendingTasks = await GetPendingTasksAsync(cancellationToken);
        
        foreach (var task in pendingTasks)
        {
            try
            {
                var agentId = await AssignTaskAsync(task, cancellationToken);
                if (agentId.HasValue)
                {
                    // Execute the task asynchronously
                    _ = Task.Run(async () => await ExecuteTaskAsync(task.Id, cancellationToken), cancellationToken);
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error processing pending task {TaskId}", task.Id);
            }
        }
    }

    public async Task<bool> RetryFailedTaskAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        var task = await _taskRepository.GetByIdAsync(taskId, cancellationToken);
        if (task == null || !task.CanRetry())
        {
            return false;
        }

        try
        {
            task.Retry();
            await _taskRepository.UpdateAsync(task, cancellationToken);
            
            _logger.LogInformation("Task {TaskId} queued for retry (attempt {RetryCount})", taskId, task.RetryCount);
            
            // Try to assign and execute again
            var agentId = await AssignTaskAsync(task, cancellationToken);
            if (agentId.HasValue)
            {
                return await ExecuteTaskAsync(taskId, cancellationToken);
            }
            
            return false;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to retry task {TaskId}", taskId);
            return false;
        }
    }

    public async Task MonitorAgentHealthAsync(CancellationToken cancellationToken = default)
    {
        var agents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
        
        foreach (var agent in agents)
        {
            try
            {
                // Perform health check - in real implementation, this might involve
                // checking if the agent's AI model is responding, etc.
                agent.RecordHealthCheck();
                await _agentRepository.UpdateAsync(agent, cancellationToken);
                
                _logger.LogDebug("Health check completed for agent {AgentId} ({AgentName})", agent.Id, agent.Name);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Health check failed for agent {AgentId}", agent.Id);
                agent.SetError($"Health check failed: {ex.Message}");
                await _agentRepository.UpdateAsync(agent, cancellationToken);
            }
        }
    }

    private double CalculateAgentScore(AIAgent agent, AgentTask task)
    {
        double score = 0;

        // Base score for being available
        if (agent.CanAcceptTask())
        {
            score += 10;
        }
        else
        {
            return 0; // Agent can't accept tasks
        }

        // Score based on capability match
        var requiredCapabilities = task.RequiredCapabilities.ToList();
        var matchedCapabilities = requiredCapabilities.Count(cap => agent.HasCapability(cap));
        
        if (requiredCapabilities.Any())
        {
            var capabilityMatchRatio = (double)matchedCapabilities / requiredCapabilities.Count;
            score += capabilityMatchRatio * 50; // Up to 50 points for capability match
        }
        else
        {
            score += 25; // Default score if no specific capabilities required
        }

        // Score based on current load (fewer current tasks = higher score)
        var loadScore = (agent.Configuration.MaxConcurrentTasks - agent.CurrentTasks.Count) * 5;
        score += loadScore;

        // Score based on success rate
        score += agent.GetSuccessRate() * 20; // Up to 20 points for high success rate

        // Score based on response time (lower = better)
        if (agent.AverageResponseTime > 0)
        {
            score += Math.Max(0, 10 - (agent.AverageResponseTime / 1000)); // Penalize slow agents
        }

        return score;
    }
}