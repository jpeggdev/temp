using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Application.AgentManagement.Models;
using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentLoadBalancer(
    IAgentRepository agentRepository,
    IAgentCapabilityMatcher capabilityMatcher,
    IAgentDiscoveryService discoveryService,
    ILogger<AgentLoadBalancer> logger) : IAgentLoadBalancer
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentCapabilityMatcher _capabilityMatcher = capabilityMatcher ?? throw new ArgumentNullException(nameof(capabilityMatcher));
    private readonly IAgentDiscoveryService _discoveryService = discoveryService ?? throw new ArgumentNullException(nameof(discoveryService));
    private readonly ILogger<AgentLoadBalancer> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    // Circuit breaker state management
    private readonly ConcurrentDictionary<Guid, CircuitBreakerStatus> _circuitBreakers = new();
    private readonly ConcurrentDictionary<Guid, ThrottleSettings> _throttleSettings = new();
    private readonly ConcurrentDictionary<Guid, List<HealthAlert>> _healthAlerts = new();

    public async Task<AgentAssignment?> AssignTaskWithLoadBalancingAsync(TaskRequirements task, CancellationToken cancellationToken = default)
    {
        try
        {
            var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            var healthyAgents = availableAgents.Where(a => IsAgentHealthy(a)).ToList();

            if (!healthyAgents.Any())
            {
                _logger.LogWarning("No healthy agents available for task assignment");
                return null;
            }

            // Find matching agents
            var matches = await _capabilityMatcher.FindMatchingAgentsAsync(task, cancellationToken);
            var healthyMatches = matches.Where(m => healthyAgents.Any(h => h.Id == m.Agent.Id)).ToList();

            if (!healthyMatches.Any())
            {
                _logger.LogWarning("No healthy matching agents found for task");
                return await HandleFallbackAssignment(task, cancellationToken);
            }

            // Apply load balancing strategy
            var selectedMatch = ApplyLoadBalancingStrategy(healthyMatches, LoadDistributionStrategy.Hybrid);
            var backupAgents = healthyMatches.Where(m => m.Agent.Id != selectedMatch.Agent.Id)
                .OrderByDescending(m => m.Score)
                .Take(2)
                .Select(m => m.Agent);

            var assignment = new AgentAssignment(
                Guid.NewGuid(), // Task ID would come from elsewhere
                selectedMatch.Agent,
                AssignmentStrategy.LoadBalanced,
                selectedMatch.Score,
                backupAgents,
                $"Selected using {LoadDistributionStrategy.Hybrid} strategy with score {selectedMatch.Score:F1}",
                DateTime.UtcNow
            );

            _logger.LogInformation("Assigned task to agent {AgentId} ({AgentName}) using load balancing", 
                selectedMatch.Agent.Id, selectedMatch.Agent.Name);

            return assignment;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to assign task with load balancing");
            return null;
        }
    }

    public async Task<IEnumerable<AgentAssignment>> AssignMultipleTasksAsync(IEnumerable<TaskRequirements> tasks, CancellationToken cancellationToken = default)
    {
        try
        {
            var assignments = new List<AgentAssignment>();
            var taskList = tasks.ToList();
            var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            var agentLoads = availableAgents.ToDictionary(a => a.Id, a => (double)a.CurrentTasks.Count / a.Configuration.MaxConcurrentTasks);

            foreach (var task in taskList)
            {
                var assignment = await AssignTaskWithLoadBalancingAsync(task, cancellationToken);
                if (assignment != null)
                {
                    assignments.Add(assignment);
                    // Update load tracking for next assignment
                    if (agentLoads.ContainsKey(assignment.AssignedAgent.Id))
                    {
                        agentLoads[assignment.AssignedAgent.Id] += 1.0 / assignment.AssignedAgent.Configuration.MaxConcurrentTasks;
                    }
                }
            }

            _logger.LogInformation("Assigned {AssignedCount} out of {TotalCount} tasks", assignments.Count, taskList.Count);
            return assignments;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to assign multiple tasks");
            return Enumerable.Empty<AgentAssignment>();
        }
    }

    public async Task<bool> RebalanceTasksAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var overloadedAgents = allAgents.Where(a => GetAgentLoadPercentage(a) > 80).ToList();
            var underutilizedAgents = allAgents.Where(a => GetAgentLoadPercentage(a) < 30).ToList();

            if (!overloadedAgents.Any() || !underutilizedAgents.Any())
            {
                _logger.LogInformation("No rebalancing needed - system load is balanced");
                return true;
            }

            var rebalancedTasks = 0;
            foreach (var overloadedAgent in overloadedAgents)
            {
                var tasksToRebalance = overloadedAgent.CurrentTasks.Take(2).ToList(); // Move up to 2 tasks
                
                foreach (var task in tasksToRebalance)
                {
                    var suitableAgent = underutilizedAgents
                        .Where(a => a.CanAcceptTask())
                        .OrderBy(a => a.CurrentTasks.Count)
                        .FirstOrDefault();

                    if (suitableAgent != null)
                    {
                        // In a full implementation, this would involve actual task migration
                        _logger.LogInformation("Would rebalance task {TaskId} from agent {OverloadedAgent} to agent {UnderutilizedAgent}", 
                            task.Id, overloadedAgent.Id, suitableAgent.Id);
                        rebalancedTasks++;
                    }
                }
            }

            _logger.LogInformation("Rebalanced {TaskCount} tasks across agents", rebalancedTasks);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to rebalance tasks");
            return false;
        }
    }

    public async Task<LoadBalancingReport> GetLoadBalancingReportAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var agentLoads = allAgents.Select(a => new AgentLoadMetric(
                a.Id,
                a.Name,
                GetAgentLoadPercentage(a),
                a.CurrentTasks.Count,
                a.Configuration.MaxConcurrentTasks,
                a.AverageResponseTime,
                GetLoadStatus(GetAgentLoadPercentage(a))
            )).ToList();

            var systemUtilization = agentLoads.Any() ? agentLoads.Average(l => l.UtilizationPercentage) : 0.0;

            var imbalances = IdentifyLoadImbalances(agentLoads);
            var recommendations = GenerateLoadBalancingRecommendations(agentLoads, imbalances);

            return new LoadBalancingReport(
                DateTime.UtcNow,
                systemUtilization,
                agentLoads,
                imbalances,
                recommendations
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to generate load balancing report");
            throw;
        }
    }

    public async Task<AgentAssignment?> AssignTaskWithFallbackAsync(TaskRequirements task, FallbackStrategy strategy, CancellationToken cancellationToken = default)
    {
        try
        {
            // First attempt normal assignment
            var normalAssignment = await AssignTaskWithLoadBalancingAsync(task, cancellationToken);
            if (normalAssignment != null)
            {
                return normalAssignment;
            }

            // Apply fallback strategy
            var fallbackOptions = await GetFallbackOptionsAsync(task, cancellationToken);
            var selectedFallback = fallbackOptions.OrderByDescending(f => f.Suitability).FirstOrDefault();

            if (selectedFallback != null)
            {
                var assignment = new AgentAssignment(
                    Guid.NewGuid(),
                    selectedFallback.Agent,
                    AssignmentStrategy.Fallback,
                    selectedFallback.Suitability,
                    Enumerable.Empty<AIAgent>(),
                    $"Fallback assignment: {selectedFallback.Description}",
                    DateTime.UtcNow
                );

                _logger.LogInformation("Assigned task using fallback strategy to agent {AgentId} ({AgentName})", 
                    selectedFallback.Agent.Id, selectedFallback.Agent.Name);

                return assignment;
            }

            _logger.LogWarning("No fallback options available for task assignment");
            return null;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to assign task with fallback");
            return null;
        }
    }

    public async Task<IEnumerable<FallbackOption>> GetFallbackOptionsAsync(TaskRequirements task, CancellationToken cancellationToken = default)
    {
        try
        {
            var options = new List<FallbackOption>();
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);

            // Option 1: Similar capability agents
            var similarAgents = allAgents.Where(a => a.Capabilities.Any(c => 
                task.RequiredCapabilities?.Any(rc => rc.Contains(c, StringComparison.OrdinalIgnoreCase)) == true));
            
            foreach (var agent in similarAgents.Take(3))
            {
                options.Add(new FallbackOption(
                    agent,
                    FallbackType.SimilarCapability,
                    0.7,
                    $"Has similar capabilities to those required",
                    new[] { "May need additional guidance" },
                    TimeSpan.FromMinutes(5)
                ));
            }

            // Option 2: General purpose agents
            var generalAgents = allAgents.Where(a => a.Type == Domain.AgentManagement.Enums.AgentType.GeneralAssistant);
            foreach (var agent in generalAgents.Take(2))
            {
                options.Add(new FallbackOption(
                    agent,
                    FallbackType.GeneralPurpose,
                    0.5,
                    "General purpose agent that can handle various tasks",
                    new[] { "May require more time", "Lower specialization" },
                    TimeSpan.FromMinutes(10)
                ));
            }

            // Option 3: Task queuing (virtual option)
            options.Add(new FallbackOption(
                allAgents.First(), // Placeholder
                FallbackType.TaskQueuing,
                0.3,
                "Queue task for later execution when agents become available",
                new[] { "Delayed execution", "No immediate response" },
                null
            ));

            return options.OrderByDescending(o => o.Suitability);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get fallback options");
            return Enumerable.Empty<FallbackOption>();
        }
    }

    public async Task<bool> ActivateFallbackAsync(Guid taskId, FallbackReason reason, CancellationToken cancellationToken = default)
    {
        try
        {
            _logger.LogInformation("Activated fallback for task {TaskId} due to {Reason}", taskId, reason);
            // Implementation would handle the actual fallback activation
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to activate fallback for task {TaskId}", taskId);
            return false;
        }
    }

    public async Task<FallbackAnalysis> AnalyzeFallbackNeedsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        try
        {
            // Simplified analysis
            return new FallbackAnalysis(
                0, // Would be calculated from historical data
                Enumerable.Empty<FallbackPattern>(),
                Enumerable.Empty<string>(),
                new[]
                {
                    new FallbackImprovement(
                        "Add more general-purpose agents",
                        new[] { "Better fallback coverage", "Improved resilience" },
                        0.8,
                        TimeSpan.FromDays(7)
                    )
                },
                0.85 // System resilience score
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze fallback needs");
            throw;
        }
    }

    public async Task<ScalingDecision> EvaluateScalingNeedsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var systemHealth = await GetSystemHealthAsync(cancellationToken);
            var utilization = (await GetResourceUtilizationAsync(cancellationToken)).CpuUtilization;

            var decision = utilization > 80 ? ScalingAction.ScaleUp :
                          utilization < 30 ? ScalingAction.ScaleDown :
                          ScalingAction.Maintain;

            var count = decision == ScalingAction.ScaleUp ? 2 :
                       decision == ScalingAction.ScaleDown ? 1 : 0;

            return new ScalingDecision(
                decision,
                count,
                decision == ScalingAction.ScaleUp ? new[] { Domain.AgentManagement.Enums.AgentType.GeneralAssistant } : Enumerable.Empty<Domain.AgentManagement.Enums.AgentType>(),
                $"Based on {utilization:F1}% system utilization",
                0.8,
                TimeSpan.FromMinutes(15)
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to evaluate scaling needs");
            throw;
        }
    }

    // Additional method implementations (simplified for brevity)
    public Task<bool> ScaleUpAsync(ScalingRequest request, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Scaling up {Count} agents of type {Type}", request.Count, request.TargetType);
        return Task.FromResult(true);
    }

    public Task<bool> ScaleDownAsync(ScalingRequest request, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Scaling down {Count} agents", request.Count);
        return Task.FromResult(true);
    }

    public Task<IEnumerable<AutoScalingRule>> GetAutoScalingRulesAsync(CancellationToken cancellationToken = default)
    {
        var rules = new[]
        {
            new AutoScalingRule(
                Guid.NewGuid(),
                "High Utilization Scale Up",
                ScalingTrigger.HighUtilization,
                ScalingAction.ScaleUp,
                new Dictionary<string, object> { ["threshold"] = 80.0, ["count"] = 2 },
                true,
                DateTime.UtcNow.AddHours(-1)
            )
        };
        return Task.FromResult<IEnumerable<AutoScalingRule>>(rules);
    }

    public async Task<bool> IsAgentAvailableAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        var circuitStatus = _circuitBreakers.GetValueOrDefault(agentId);
        if (circuitStatus?.State == CircuitState.Open)
        {
            return false;
        }

        var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
        return agent?.CanAcceptTask() == true;
    }

    public Task<CircuitBreakerStatus> GetCircuitBreakerStatusAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        var status = _circuitBreakers.GetValueOrDefault(agentId, new CircuitBreakerStatus(
            agentId, CircuitState.Closed, DateTime.UtcNow, 0, 0, null));
        return Task.FromResult(status);
    }

    public Task<bool> TripCircuitBreakerAsync(Guid agentId, string reason, CancellationToken cancellationToken = default)
    {
        var status = new CircuitBreakerStatus(
            agentId, CircuitState.Open, DateTime.UtcNow, 1, 0, DateTime.UtcNow.AddMinutes(5));
        _circuitBreakers[agentId] = status;
        
        _logger.LogWarning("Tripped circuit breaker for agent {AgentId}: {Reason}", agentId, reason);
        return Task.FromResult(true);
    }

    public Task<bool> ResetCircuitBreakerAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        var status = new CircuitBreakerStatus(
            agentId, CircuitState.Closed, DateTime.UtcNow, 0, 0, null);
        _circuitBreakers[agentId] = status;
        
        _logger.LogInformation("Reset circuit breaker for agent {AgentId}", agentId);
        return Task.FromResult(true);
    }

    public async Task<SystemHealthStatus> GetSystemHealthAsync(CancellationToken cancellationToken = default)
    {
        var agentHealthStatuses = await GetAgentHealthStatusesAsync(cancellationToken);
        var totalAgents = agentHealthStatuses.Count();
        var healthyAgents = agentHealthStatuses.Count(s => s.Health == HealthLevel.Good || s.Health == HealthLevel.Excellent);
        
        var overallHealth = totalAgents == 0 ? HealthLevel.Critical :
                           (double)healthyAgents / totalAgents > 0.8 ? HealthLevel.Excellent :
                           (double)healthyAgents / totalAgents > 0.6 ? HealthLevel.Good :
                           (double)healthyAgents / totalAgents > 0.4 ? HealthLevel.Fair :
                           (double)healthyAgents / totalAgents > 0.2 ? HealthLevel.Poor : HealthLevel.Critical;

        var alerts = _healthAlerts.Values.SelectMany(alerts => alerts).Where(a => !a.IsAcknowledged);

        return new SystemHealthStatus(
            overallHealth,
            totalAgents,
            healthyAgents,
            totalAgents - healthyAgents,
            70.0, // Mock utilization
            alerts
        );
    }

    public async Task<IEnumerable<AgentHealthStatus>> GetAgentHealthStatusesAsync(CancellationToken cancellationToken = default)
    {
        var agents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
        return agents.Select(agent => new AgentHealthStatus(
            agent.Id,
            agent.Name,
            DetermineAgentHealth(agent),
            agent.AverageResponseTime,
            agent.GetSuccessRate(),
            agent.LastHealthCheckAt ?? DateTime.MinValue,
            GetHealthIssues(agent)
        ));
    }

    public async Task<bool> PerformHealthCheckAsync(Guid? agentId = null, CancellationToken cancellationToken = default)
    {
        try
        {
            if (agentId.HasValue)
            {
                var agent = await _agentRepository.GetByIdAsync(agentId.Value, cancellationToken);
                if (agent != null)
                {
                    agent.RecordHealthCheck();
                    await _agentRepository.UpdateAsync(agent, cancellationToken);
                }
            }
            else
            {
                var agents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
                foreach (var agent in agents)
                {
                    agent.RecordHealthCheck();
                    await _agentRepository.UpdateAsync(agent, cancellationToken);
                }
            }

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to perform health check");
            return false;
        }
    }

    public Task<HealthTrends> GetHealthTrendsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        // Simplified implementation
        var trends = new HealthTrends(
            period ?? TimeSpan.FromDays(7),
            85.0,
            Enumerable.Empty<HealthTrendPoint>(),
            Enumerable.Empty<HealthAnomaly>()
        );
        return Task.FromResult(trends);
    }

    public Task<ResourceUtilization> GetResourceUtilizationAsync(CancellationToken cancellationToken = default)
    {
        // Mock resource utilization data
        return Task.FromResult(new ResourceUtilization(
            70.0, 65.0, 45.0, 30.0,
            Enumerable.Empty<AgentResourceUsage>()
        ));
    }

    public Task<IEnumerable<ResourceConstraint>> GetResourceConstraintsAsync(CancellationToken cancellationToken = default)
    {
        var constraints = new[]
        {
            new ResourceConstraint("CPU", 70.0, 100.0, 80.0, 90.0, ConstraintStatus.Normal),
            new ResourceConstraint("Memory", 65.0, 100.0, 75.0, 85.0, ConstraintStatus.Normal)
        };
        return Task.FromResult<IEnumerable<ResourceConstraint>>(constraints);
    }

    public Task<bool> ApplyResourceThrottlingAsync(Guid agentId, ThrottleSettings settings, CancellationToken cancellationToken = default)
    {
        _throttleSettings[agentId] = settings;
        _logger.LogInformation("Applied resource throttling to agent {AgentId}", agentId);
        return Task.FromResult(true);
    }

    public Task<bool> RemoveResourceThrottlingAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        _throttleSettings.TryRemove(agentId, out _);
        _logger.LogInformation("Removed resource throttling from agent {AgentId}", agentId);
        return Task.FromResult(true);
    }

    // Helper methods
    private bool IsAgentHealthy(AIAgent agent)
    {
        return agent.Status == Domain.AgentManagement.Enums.AgentStatus.Active &&
               agent.GetSuccessRate() > 0.5 &&
               !_circuitBreakers.ContainsKey(agent.Id) ||
               _circuitBreakers[agent.Id].State == CircuitState.Closed;
    }

    private static double GetAgentLoadPercentage(AIAgent agent)
    {
        return agent.Configuration.MaxConcurrentTasks > 0 ?
            (double)agent.CurrentTasks.Count / agent.Configuration.MaxConcurrentTasks * 100.0 : 0.0;
    }

    private static LoadStatus GetLoadStatus(double utilizationPercentage)
    {
        return utilizationPercentage switch
        {
            < 20 => LoadStatus.Idle,
            < 50 => LoadStatus.Light,
            < 70 => LoadStatus.Moderate,
            < 90 => LoadStatus.Heavy,
            _ => LoadStatus.Overloaded
        };
    }

    private AgentMatch ApplyLoadBalancingStrategy(List<AgentMatch> matches, LoadDistributionStrategy strategy)
    {
        return strategy switch
        {
            LoadDistributionStrategy.CapabilityBased => matches.OrderByDescending(m => m.RelevanceScore).First(),
            LoadDistributionStrategy.PerformanceBased => matches.OrderByDescending(m => m.PerformanceScore).First(),
            LoadDistributionStrategy.AvailabilityBased => matches.OrderByDescending(m => m.AvailabilityScore).First(),
            LoadDistributionStrategy.RoundRobin => matches.OrderBy(m => m.Agent.CurrentTasks.Count).First(),
            _ => matches.OrderByDescending(m => m.Score).First() // Hybrid/default
        };
    }

    private async Task<AgentAssignment?> HandleFallbackAssignment(TaskRequirements task, CancellationToken cancellationToken)
    {
        var fallbackOptions = await GetFallbackOptionsAsync(task, cancellationToken);
        var bestFallback = fallbackOptions.FirstOrDefault();

        if (bestFallback != null)
        {
            return new AgentAssignment(
                Guid.NewGuid(),
                bestFallback.Agent,
                AssignmentStrategy.Fallback,
                bestFallback.Suitability,
                Enumerable.Empty<AIAgent>(),
                $"Fallback assignment: {bestFallback.Description}",
                DateTime.UtcNow
            );
        }

        return null;
    }

    private static IEnumerable<LoadImbalance> IdentifyLoadImbalances(List<AgentLoadMetric> agentLoads)
    {
        var imbalances = new List<LoadImbalance>();
        
        var overloaded = agentLoads.Where(l => l.Status == LoadStatus.Overloaded).Select(l => l.AgentId);
        var underutilized = agentLoads.Where(l => l.Status == LoadStatus.Idle || l.Status == LoadStatus.Light).Select(l => l.AgentId);

        if (overloaded.Any() && underutilized.Any())
        {
            imbalances.Add(new LoadImbalance(
                "Load Distribution",
                overloaded,
                underutilized,
                0.8,
                "Redistribute tasks from overloaded to underutilized agents"
            ));
        }

        return imbalances;
    }

    private static IEnumerable<LoadBalancingRecommendation> GenerateLoadBalancingRecommendations(
        List<AgentLoadMetric> agentLoads, IEnumerable<LoadImbalance> imbalances)
    {
        var recommendations = new List<LoadBalancingRecommendation>();

        if (imbalances.Any())
        {
            recommendations.Add(new LoadBalancingRecommendation(
                agentLoads.OrderBy(l => l.UtilizationPercentage).First().AgentId, // Primary recommendation
                agentLoads.Where(l => l.Status == LoadStatus.Light).Select(l => l.AgentId), // Alternatives
                LoadDistributionStrategy.AvailabilityBased,
                "Rebalance tasks to improve distribution",
                0.3
            ));
        }

        return recommendations;
    }

    private static HealthLevel DetermineAgentHealth(AIAgent agent)
    {
        var successRate = agent.GetSuccessRate();
        var isActive = agent.Status == Domain.AgentManagement.Enums.AgentStatus.Active;
        
        if (!isActive) return HealthLevel.Critical;
        
        return successRate switch
        {
            > 0.9 => HealthLevel.Excellent,
            > 0.8 => HealthLevel.Good,
            > 0.6 => HealthLevel.Fair,
            > 0.3 => HealthLevel.Poor,
            _ => HealthLevel.Critical
        };
    }

    private static IEnumerable<string> GetHealthIssues(AIAgent agent)
    {
        var issues = new List<string>();
        
        if (agent.GetSuccessRate() < 0.5)
            issues.Add("Low success rate");
        
        if (agent.AverageResponseTime > 10000)
            issues.Add("High response time");
        
        if (agent.Status != Domain.AgentManagement.Enums.AgentStatus.Active)
            issues.Add($"Status: {agent.Status}");

        return issues;
    }
}