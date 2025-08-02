using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Domain.AgentManagement.ValueObjects;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentManager(
    IAgentRepository agentRepository,
    IAgentTaskRepository taskRepository,
    ILogger<AgentManager> logger) : IAgentManager
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentTaskRepository _taskRepository = taskRepository ?? throw new ArgumentNullException(nameof(taskRepository));
    private readonly ILogger<AgentManager> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    public async Task<Guid> CreateAgentAsync(string name, AgentType type, string? description = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var configuration = AgentConfiguration.Create(GetDefaultModelForType(type));
            var agent = AIAgent.Create(name, type, configuration, description);

            // Add default specializations based on agent type
            AddDefaultSpecializations(agent, type);
            AddDefaultCapabilities(agent, type);

            await _agentRepository.AddAsync(agent, cancellationToken);
            
            _logger.LogInformation("Created new agent {AgentId} of type {AgentType} with name '{AgentName}'", 
                agent.Id, type, name);
            
            return agent.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create agent with name '{AgentName}' and type {AgentType}", name, type);
            throw;
        }
    }

    public async Task<bool> ActivateAgentAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for activation", agentId);
                return false;
            }

            agent.Activate();
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Agent {AgentId} ({AgentName}) activated", agentId, agent.Name);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to activate agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> DeactivateAgentAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for deactivation", agentId);
                return false;
            }

            agent.Deactivate();
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Agent {AgentId} ({AgentName}) deactivated", agentId, agent.Name);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to deactivate agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> SetAgentMaintenanceAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for maintenance", agentId);
                return false;
            }

            agent.SetMaintenance();
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Agent {AgentId} ({AgentName}) set to maintenance mode", agentId, agent.Name);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to set agent {AgentId} to maintenance", agentId);
            return false;
        }
    }

    public async Task<bool> RetireAgentAsync(Guid agentId, string reason, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for retirement", agentId);
                return false;
            }

            // Complete any active tasks first
            foreach (var task in agent.CurrentTasks.ToList())
            {
                agent.FailTask(task.Id, $"Agent retired: {reason}");
            }

            agent.Deactivate();
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Agent {AgentId} ({AgentName}) retired. Reason: {Reason}", 
                agentId, agent.Name, reason);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to retire agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> UpdateAgentConfigurationAsync(Guid agentId, AgentConfiguration configuration, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for configuration update", agentId);
                return false;
            }

            agent.UpdateConfiguration(configuration);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Agent {AgentId} configuration updated", agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update configuration for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> AddAgentCapabilityAsync(Guid agentId, string capability, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for capability addition", agentId);
                return false;
            }

            agent.AddCapability(capability);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Added capability '{Capability}' to agent {AgentId}", capability, agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to add capability '{Capability}' to agent {AgentId}", capability, agentId);
            return false;
        }
    }

    public async Task<bool> RemoveAgentCapabilityAsync(Guid agentId, string capability, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for capability removal", agentId);
                return false;
            }

            agent.RemoveCapability(capability);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Removed capability '{Capability}' from agent {AgentId}", capability, agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove capability '{Capability}' from agent {AgentId}", capability, agentId);
            return false;
        }
    }

    public async Task<bool> AddAgentSpecializationAsync(Guid agentId, AgentSpecialization specialization, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for specialization addition", agentId);
                return false;
            }

            agent.AddSpecialization(specialization);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Added specialization '{Specialization}' to agent {AgentId}", 
                specialization.ToString(), agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to add specialization to agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> RemoveAgentSpecializationAsync(Guid agentId, string domain, string subdomain, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for specialization removal", agentId);
                return false;
            }

            agent.RemoveSpecialization(domain, subdomain);
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Removed specialization '{Domain}/{Subdomain}' from agent {AgentId}", 
                domain, subdomain, agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove specialization '{Domain}/{Subdomain}' from agent {AgentId}", 
                domain, subdomain, agentId);
            return false;
        }
    }

    public async Task<IEnumerable<AIAgent>> GetAgentsByTypeAsync(AgentType type, CancellationToken cancellationToken = default)
    {
        try
        {
            return await _agentRepository.GetAgentsByTypeAsync(type, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get agents by type {AgentType}", type);
            return Enumerable.Empty<AIAgent>();
        }
    }

    public async Task<IEnumerable<AIAgent>> GetAgentsByCapabilityAsync(string capability, CancellationToken cancellationToken = default)
    {
        try
        {
            return await _agentRepository.GetAgentsWithCapabilitiesAsync(new[] { capability }, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get agents by capability '{Capability}'", capability);
            return Enumerable.Empty<AIAgent>();
        }
    }

    public async Task<IEnumerable<AIAgent>> GetAgentsBySpecializationAsync(string domain, string? subdomain = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var agents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            return agents.Where(a => a.HasSpecializationIn(domain) && 
                (subdomain == null || a.GetSpecialization(domain, subdomain) != null));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get agents by specialization '{Domain}/{Subdomain}'", domain, subdomain ?? "any");
            return Enumerable.Empty<AIAgent>();
        }
    }

    public async Task<IEnumerable<AIAgent>> GetAvailableAgentsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            return await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get available agents");
            return Enumerable.Empty<AIAgent>();
        }
    }

    public async Task<AIAgent?> FindBestAgentForTaskAsync(string taskDescription, string? domain = null, string? subdomain = null, IEnumerable<string>? keywords = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            
            if (!availableAgents.Any())
            {
                _logger.LogWarning("No available agents found for task matching");
                return null;
            }

            var scoredAgents = availableAgents.Select(agent => new
            {
                Agent = agent,
                Score = CalculateAgentTaskScore(agent, domain, subdomain, keywords)
            })
            .Where(x => x.Score > 0)
            .OrderByDescending(x => x.Score)
            .ToList();

            var bestAgent = scoredAgents.FirstOrDefault()?.Agent;
            
            if (bestAgent != null)
            {
                _logger.LogInformation("Selected agent {AgentId} ({AgentName}) for task with score {Score}", 
                    bestAgent.Id, bestAgent.Name, scoredAgents.First().Score);
            }

            return bestAgent;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to find best agent for task");
            return null;
        }
    }

    public async Task<bool> RecordAgentPerformanceAsync(Guid agentId, TimeSpan responseTime, bool successful, string? feedback = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for performance recording", agentId);
                return false;
            }

            // This would normally be handled by task completion, but this method allows for external performance tracking
            _logger.LogInformation("Recorded performance for agent {AgentId}: Success={Success}, ResponseTime={ResponseTime}ms, Feedback='{Feedback}'", 
                agentId, successful, responseTime.TotalMilliseconds, feedback ?? "none");
            
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to record performance for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> PerformHealthCheckAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for health check", agentId);
                return false;
            }

            agent.RecordHealthCheck();
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogDebug("Health check completed for agent {AgentId} ({AgentName})", agentId, agent.Name);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Health check failed for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task PerformAllHealthChecksAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var agents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var healthCheckTasks = agents.Select(agent => PerformHealthCheckAsync(agent.Id, cancellationToken));
            
            await Task.WhenAll(healthCheckTasks);
            
            _logger.LogInformation("Completed health checks for {AgentCount} agents", agents.Count());
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to perform health checks for all agents");
        }
    }

    public async Task<AgentPerformanceMetrics> GetAgentPerformanceMetricsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                throw new ArgumentException($"Agent {agentId} not found");
            }

            var totalTasks = agent.SuccessfulTasksCount + agent.FailedTasksCount;
            var topSpecializations = agent.Specializations
                .OrderByDescending(s => s.UsageCount)
                .ThenByDescending(s => s.SkillLevel * s.Confidence)
                .Take(5);

            return new AgentPerformanceMetrics(
                agent.Id,
                agent.Name,
                totalTasks,
                agent.SuccessfulTasksCount,
                agent.FailedTasksCount,
                agent.GetSuccessRate(),
                agent.AverageResponseTime,
                agent.LastActiveAt ?? DateTime.MinValue,
                topSpecializations);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get performance metrics for agent {AgentId}", agentId);
            throw;
        }
    }

    public async Task<bool> UpdateAgentFromFeedbackAsync(Guid agentId, string taskDomain, string feedback, bool successful, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for feedback update", agentId);
                return false;
            }

            // Update specializations based on feedback
            var domainSpecializations = agent.GetSpecializationsByDomain(taskDomain);
            foreach (var specialization in domainSpecializations)
            {
                // Adjust confidence based on success/failure
                var confidenceAdjustment = successful ? 0.05 : -0.02;
                var newConfidence = Math.Max(0.0, Math.Min(1.0, specialization.Confidence + confidenceAdjustment));
                
                var updatedSpecialization = specialization.UpdateConfidence(newConfidence).RecordUsage();
                agent.AddSpecialization(updatedSpecialization);
            }

            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Updated agent {AgentId} from feedback for domain '{Domain}': Success={Success}", 
                agentId, taskDomain, successful);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update agent {AgentId} from feedback", agentId);
            return false;
        }
    }

    public async Task<bool> EnhanceAgentSpecializationAsync(Guid agentId, string domain, string subdomain, int skillImprovement, double confidenceAdjustment, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for specialization enhancement", agentId);
                return false;
            }

            var specialization = agent.GetSpecialization(domain, subdomain);
            if (specialization == null)
            {
                _logger.LogWarning("Specialization '{Domain}/{Subdomain}' not found for agent {AgentId}", 
                    domain, subdomain, agentId);
                return false;
            }

            var newSkillLevel = Math.Max(1, Math.Min(10, specialization.SkillLevel + skillImprovement));
            var newConfidence = Math.Max(0.0, Math.Min(1.0, specialization.Confidence + confidenceAdjustment));

            var enhanced = specialization.UpdateSkillLevel(newSkillLevel).UpdateConfidence(newConfidence);
            agent.AddSpecialization(enhanced);
            
            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Enhanced specialization '{Domain}/{Subdomain}' for agent {AgentId}: Skill {OldSkill}->{NewSkill}, Confidence {OldConf:P}->{NewConf:P}", 
                domain, subdomain, agentId, specialization.SkillLevel, newSkillLevel, specialization.Confidence, newConfidence);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to enhance specialization for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<IEnumerable<AIAgent>> GetCompatibleAgentsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for compatibility check", agentId);
                return Enumerable.Empty<AIAgent>();
            }

            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            
            // Find agents with complementary specializations
            var compatibleAgents = allAgents
                .Where(a => a.Id != agentId)
                .Where(a => HasComplementarySpecializations(agent, a))
                .ToList();

            return compatibleAgents;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get compatible agents for {AgentId}", agentId);
            return Enumerable.Empty<AIAgent>();
        }
    }

    public async Task<bool> CanAgentsCollaborateAsync(Guid agentId1, Guid agentId2, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent1 = await _agentRepository.GetByIdAsync(agentId1, cancellationToken);
            var agent2 = await _agentRepository.GetByIdAsync(agentId2, cancellationToken);

            if (agent1 == null || agent2 == null)
            {
                return false;
            }

            // Check if both agents are available and have complementary skills
            return agent1.CanAcceptTask() && 
                   agent2.CanAcceptTask() && 
                   HasComplementarySpecializations(agent1, agent2);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to check collaboration compatibility between agents {AgentId1} and {AgentId2}", 
                agentId1, agentId2);
            return false;
        }
    }

    public async Task<AgentTeam> FormAgentTeamAsync(string teamName, IEnumerable<Guid> agentIds, string objective, CancellationToken cancellationToken = default)
    {
        try
        {
            var agentIdList = agentIds.ToList();
            var agents = new List<AIAgent>();

            foreach (var agentId in agentIdList)
            {
                var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
                if (agent != null && agent.CanAcceptTask())
                {
                    agents.Add(agent);
                }
            }

            if (agents.Count != agentIdList.Count)
            {
                _logger.LogWarning("Not all agents are available for team formation. Requested: {Requested}, Available: {Available}", 
                    agentIdList.Count, agents.Count);
            }

            var team = new AgentTeam(
                Guid.NewGuid(),
                teamName,
                objective,
                agents.Select(a => a.Id),
                DateTime.UtcNow);

            _logger.LogInformation("Formed agent team '{TeamName}' with {AgentCount} agents for objective: {Objective}", 
                teamName, agents.Count, objective);

            return team;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to form agent team '{TeamName}'", teamName);
            throw;
        }
    }

    private static string GetDefaultModelForType(AgentType type)
    {
        return type switch
        {
            AgentType.CodeAgent or AgentType.ReviewAgent or AgentType.TestingAgent or AgentType.DebuggingAgent => "gpt-4",
            AgentType.WritingAgent or AgentType.DocumentationAgent => "gpt-4",
            AgentType.AnalysisAgent or AgentType.DataAnalyst => "gpt-4",
            AgentType.ResearchAgent => "gpt-4",
            _ => "gpt-3.5-turbo"
        };
    }

    private static void AddDefaultSpecializations(AIAgent agent, AgentType type)
    {
        switch (type)
        {
            case AgentType.CodeAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.CSharpDevelopment());
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.WebDevelopment());
                break;
            case AgentType.WritingAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.TechnicalWriting());
                break;
            case AgentType.AnalysisAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.DataAnalysis());
                break;
            case AgentType.PlanningAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.ProjectPlanning());
                break;
            case AgentType.ResearchAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.ResearchAndInvestigation());
                break;
            case AgentType.AutomationAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.ProcessAutomation());
                break;
            case AgentType.ReviewAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.CodeReview());
                break;
            case AgentType.TestingAgent:
                agent.AddSpecialization(AgentSpecialization.CommonSpecializations.Testing());
                break;
        }
    }

    private static void AddDefaultCapabilities(AIAgent agent, AgentType type)
    {
        switch (type)
        {
            case AgentType.CodeAgent:
                agent.AddCapability("code-generation");
                agent.AddCapability("refactoring");
                agent.AddCapability("debugging");
                break;
            case AgentType.WritingAgent:
                agent.AddCapability("content-creation");
                agent.AddCapability("editing");
                agent.AddCapability("documentation");
                break;
            case AgentType.AnalysisAgent:
                agent.AddCapability("data-analysis");
                agent.AddCapability("pattern-recognition");
                agent.AddCapability("reporting");
                break;
            case AgentType.PlanningAgent:
                agent.AddCapability("task-planning");
                agent.AddCapability("resource-allocation");
                agent.AddCapability("scheduling");
                break;
            case AgentType.ResearchAgent:
                agent.AddCapability("information-gathering");
                agent.AddCapability("fact-checking");
                agent.AddCapability("synthesis");
                break;
            case AgentType.AutomationAgent:
                agent.AddCapability("workflow-automation");
                agent.AddCapability("script-generation");
                agent.AddCapability("process-optimization");
                break;
        }
    }

    private double CalculateAgentTaskScore(AIAgent agent, string? domain, string? subdomain, IEnumerable<string>? keywords)
    {
        double score = 0.0;

        // Base availability score
        if (agent.CanAcceptTask())
        {
            score += 20.0;
        }
        else
        {
            return 0.0; // Agent cannot accept tasks
        }

        // Specialization relevance score
        if (!string.IsNullOrWhiteSpace(domain))
        {
            score += agent.CalculateTaskRelevanceScore(domain, subdomain, keywords);
        }

        // Performance-based scoring
        score += agent.GetSuccessRate() * 20.0; // Up to 20 points for high success rate
        score += Math.Max(0, 10.0 - (agent.AverageResponseTime / 1000.0)); // Penalty for slow response
        score += (agent.Configuration.MaxConcurrentTasks - agent.CurrentTasks.Count) * 5.0; // Load balancing

        // Recent activity bonus
        if (agent.LastActiveAt.HasValue && agent.LastActiveAt > DateTime.UtcNow.AddHours(-24))
        {
            score += 5.0;
        }

        return Math.Min(100.0, score);
    }

    private static bool HasComplementarySpecializations(AIAgent agent1, AIAgent agent2)
    {
        var agent1Domains = agent1.Specializations.Select(s => s.Domain).Distinct().ToHashSet();
        var agent2Domains = agent2.Specializations.Select(s => s.Domain).Distinct().ToHashSet();

        // Agents are complementary if they have different specialization domains
        // or if they have overlapping domains with different subdomains
        return !agent1Domains.SetEquals(agent2Domains) || 
               agent1.Specializations.Any(s1 => agent2.Specializations.Any(s2 => 
                   s1.Domain == s2.Domain && s1.Subdomain != s2.Subdomain));
    }
}