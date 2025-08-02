using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentCapabilityMatcher(
    IAgentRepository agentRepository,
    ILogger<AgentCapabilityMatcher> logger) : IAgentCapabilityMatcher
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly ILogger<AgentCapabilityMatcher> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    public async Task<IEnumerable<AgentMatch>> FindMatchingAgentsAsync(TaskRequirements requirements, CancellationToken cancellationToken = default)
    {
        try
        {
            var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            
            if (!availableAgents.Any())
            {
                _logger.LogWarning("No available agents found for task matching");
                return Enumerable.Empty<AgentMatch>();
            }

            var matches = new List<AgentMatch>();

            foreach (var agent in availableAgents)
            {
                var match = await EvaluateAgentMatchAsync(agent, requirements, cancellationToken);
                if (match.Score > 0)
                {
                    matches.Add(match);
                }
            }

            return matches.OrderByDescending(m => m.Score);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to find matching agents for task requirements");
            return Enumerable.Empty<AgentMatch>();
        }
    }

    public async Task<AgentMatch?> FindBestMatchAsync(TaskRequirements requirements, CancellationToken cancellationToken = default)
    {
        try
        {
            var matches = await FindMatchingAgentsAsync(requirements, cancellationToken);
            var bestMatch = matches.FirstOrDefault();

            if (bestMatch != null)
            {
                _logger.LogInformation("Found best match: Agent {AgentId} ({AgentName}) with score {Score} for task in domain '{Domain}'", 
                    bestMatch.Agent.Id, bestMatch.Agent.Name, bestMatch.Score, requirements.Domain ?? "unspecified");
            }
            else
            {
                _logger.LogWarning("No suitable agent match found for task requirements");
            }

            return bestMatch;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to find best agent match");
            return null;
        }
    }

    public async Task<IEnumerable<AgentMatch>> FindBackupAgentsAsync(TaskRequirements requirements, int maxResults = 3, CancellationToken cancellationToken = default)
    {
        try
        {
            var matches = await FindMatchingAgentsAsync(requirements, cancellationToken);
            
            // Skip the best match and return the next best options
            return matches.Skip(1).Take(maxResults);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to find backup agents");
            return Enumerable.Empty<AgentMatch>();
        }
    }

    public async Task<bool> CanAgentHandleTaskAsync(Guid agentId, TaskRequirements requirements, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null || !agent.CanAcceptTask())
            {
                return false;
            }

            var match = await EvaluateAgentMatchAsync(agent, requirements, cancellationToken);
            
            // Consider agent capable if score is above threshold
            const double minimumScore = 30.0;
            return match.Score >= minimumScore;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to evaluate if agent {AgentId} can handle task", agentId);
            return false;
        }
    }

    public async Task<AgentCapabilityAnalysis> AnalyzeAgentCapabilitiesAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                throw new ArgumentException($"Agent {agentId} not found");
            }

            var capabilities = agent.Capabilities.Select(cap => new CapabilityAssessment(
                cap,
                CalculateCapabilityStrength(agent, cap),
                0, // Usage count would need to be tracked separately
                null // Last used would need to be tracked separately
            ));

            var specializations = agent.Specializations.Select(spec => new SpecializationAssessment(
                spec.Domain,
                spec.Subdomain,
                spec.SkillLevel,
                spec.Confidence,
                spec.UsageCount,
                spec.LastUsedAt,
                spec.Keywords
            ));

            var performanceProfile = new PerformanceProfile(
                agent.GetSuccessRate(),
                agent.AverageResponseTime,
                agent.SuccessfulTasksCount + agent.FailedTasksCount,
                agent.LastActiveAt,
                Enumerable.Empty<PerformanceTrend>() // Would need historical data
            );

            var availabilityProfile = new AvailabilityProfile(
                agent.CanAcceptTask(),
                agent.CurrentTasks.Count,
                agent.Configuration.MaxConcurrentTasks,
                EstimateTimeUntilFree(agent),
                Enumerable.Empty<DateTime>() // Would need historical data
            );

            return new AgentCapabilityAnalysis(
                agent.Id,
                agent.Name,
                capabilities,
                specializations,
                performanceProfile,
                availabilityProfile
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze capabilities for agent {AgentId}", agentId);
            throw;
        }
    }

    public async Task<TeamFormationSuggestion> SuggestTeamForComplexTaskAsync(ComplexTaskRequirements requirements, CancellationToken cancellationToken = default)
    {
        try
        {
            var availableAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            var componentAssignments = new List<(TaskComponent Component, AgentMatch Agent)>();
            var usedAgents = new HashSet<Guid>();

            // Assign agents to each component
            foreach (var component in requirements.Components)
            {
                var componentMatches = await FindMatchingAgentsAsync(component.Requirements, cancellationToken);
                var bestAvailableMatch = componentMatches.FirstOrDefault(m => !usedAgents.Contains(m.Agent.Id));

                if (bestAvailableMatch != null)
                {
                    componentAssignments.Add((component, bestAvailableMatch));
                    
                    // Mark agent as used unless parallel execution is allowed
                    if (!requirements.AllowParallelExecution)
                    {
                        usedAgents.Add(bestAvailableMatch.Agent.Id);
                    }
                }
            }

            // Create team members
            var teamMembers = componentAssignments
                .GroupBy(ca => ca.Agent.Agent.Id)
                .Select(group =>
                {
                    var agent = group.First().Agent.Agent;
                    var assignedComponents = group.Select(g => g.Component);
                    var role = DetermineTeamRole(agent, assignedComponents);
                    var contributionScore = group.Average(g => g.Agent.Score);

                    return new AgentTeamMember(agent, role, assignedComponents, contributionScore);
                });

            var teamScore = teamMembers.Any() ? teamMembers.Average(tm => tm.ContributionScore) : 0.0;
            var rationale = GenerateTeamRationale(teamMembers, requirements);
            var estimatedTime = EstimateTeamExecutionTime(requirements, teamMembers);
            var risks = IdentifyTeamRisks(teamMembers, requirements);

            return new TeamFormationSuggestion(
                teamMembers,
                teamScore,
                rationale,
                requirements.Components,
                estimatedTime,
                risks
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to suggest team for complex task");
            throw;
        }
    }

    private async Task<AgentMatch> EvaluateAgentMatchAsync(AIAgent agent, TaskRequirements requirements, CancellationToken cancellationToken)
    {
        var matchedCapabilities = new List<string>();
        var matchedSpecializations = new List<string>();
        var concerns = new List<string>();

        // Calculate availability score
        var availabilityScore = CalculateAvailabilityScore(agent);
        if (availabilityScore == 0)
        {
            concerns.Add("Agent is not available");
        }

        // Calculate performance score
        var performanceScore = CalculatePerformanceScore(agent);
        if (performanceScore < 50)
        {
            concerns.Add("Agent has below-average performance");
        }

        // Calculate relevance score
        var relevanceScore = CalculateRelevanceScore(agent, requirements, matchedCapabilities, matchedSpecializations);
        if (relevanceScore < 30)
        {
            concerns.Add("Agent has limited relevant experience");
        }

        // Apply requirements filters
        if (requirements.MinSkillLevel.HasValue && agent.GetAverageSkillLevel() < requirements.MinSkillLevel.Value)
        {
            concerns.Add($"Agent skill level ({agent.GetAverageSkillLevel()}) below required ({requirements.MinSkillLevel.Value})");
        }

        if (requirements.MinConfidence.HasValue && agent.GetAverageConfidence() < requirements.MinConfidence.Value)
        {
            concerns.Add($"Agent confidence ({agent.GetAverageConfidence():P}) below required ({requirements.MinConfidence.Value:P})");
        }

        // Calculate total score
        var totalScore = (availabilityScore * 0.3) + (performanceScore * 0.3) + (relevanceScore * 0.4);

        // Apply priority multiplier
        totalScore *= GetPriorityMultiplier(requirements.Priority);

        // Determine primary match reason
        var primaryReason = DeterminePrimaryMatchReason(relevanceScore, matchedCapabilities, matchedSpecializations);

        return new AgentMatch(
            agent,
            totalScore,
            primaryReason,
            matchedCapabilities,
            matchedSpecializations,
            availabilityScore,
            performanceScore,
            relevanceScore,
            concerns
        );
    }

    private static double CalculateAvailabilityScore(AIAgent agent)
    {
        if (!agent.CanAcceptTask())
        {
            return 0.0;
        }

        // Score based on current load
        var loadRatio = (double)agent.CurrentTasks.Count / agent.Configuration.MaxConcurrentTasks;
        var loadScore = (1.0 - loadRatio) * 100.0;

        // Bonus for recent activity
        var activityBonus = 0.0;
        if (agent.LastActiveAt.HasValue)
        {
            var hoursSinceActive = (DateTime.UtcNow - agent.LastActiveAt.Value).TotalHours;
            if (hoursSinceActive < 1)
                activityBonus = 10.0;
            else if (hoursSinceActive < 24)
                activityBonus = 5.0;
        }

        return Math.Min(100.0, loadScore + activityBonus);
    }

    private static double CalculatePerformanceScore(AIAgent agent)
    {
        var successRate = agent.GetSuccessRate();
        var baseScore = successRate * 70.0; // Up to 70 points for success rate

        // Response time penalty
        var responseTimePenalty = 0.0;
        if (agent.AverageResponseTime > 5000) // More than 5 seconds
        {
            responseTimePenalty = Math.Min(20.0, (agent.AverageResponseTime - 5000) / 1000.0);
        }

        // Experience bonus
        var totalTasks = agent.SuccessfulTasksCount + agent.FailedTasksCount;
        var experienceBonus = Math.Min(20.0, totalTasks / 10.0);

        return Math.Max(0.0, baseScore + experienceBonus - responseTimePenalty);
    }

    private static double CalculateRelevanceScore(AIAgent agent, TaskRequirements requirements, 
        List<string> matchedCapabilities, List<string> matchedSpecializations)
    {
        double score = 0.0;

        // Capability matching
        var requiredCapabilities = requirements.RequiredCapabilities?.ToList() ?? new List<string>();
        foreach (var capability in requiredCapabilities)
        {
            if (agent.HasCapability(capability))
            {
                matchedCapabilities.Add(capability);
                score += 15.0; // 15 points per matched capability
            }
        }

        // Specialization matching
        if (!string.IsNullOrWhiteSpace(requirements.Domain))
        {
            var relevanceScore = agent.CalculateTaskRelevanceScore(
                requirements.Domain, 
                requirements.Subdomain, 
                requirements.Keywords);
                
            score += relevanceScore * 0.6; // Scale specialization score

            if (relevanceScore > 50)
            {
                var bestSpec = agent.Specializations
                    .Where(s => s.Domain.Equals(requirements.Domain, StringComparison.OrdinalIgnoreCase))
                    .OrderByDescending(s => s.CalculateRelevanceScore(requirements.Domain, requirements.Subdomain, requirements.Keywords))
                    .FirstOrDefault();

                if (bestSpec != null)
                {
                    matchedSpecializations.Add($"{bestSpec.Domain}/{bestSpec.Subdomain}");
                }
            }
        }

        // Agent type matching
        if (IsAgentTypeRelevant(agent.Type, requirements.Domain))
        {
            score += 10.0;
        }

        return Math.Min(100.0, score);
    }

    private static bool IsAgentTypeRelevant(Domain.AgentManagement.Enums.AgentType agentType, string? taskDomain)
    {
        if (string.IsNullOrWhiteSpace(taskDomain))
            return true;

        var domain = taskDomain.ToLowerInvariant();
        
        return agentType switch
        {
            Domain.AgentManagement.Enums.AgentType.CodeAgent => domain.Contains("code") || domain.Contains("programming") || domain.Contains("development"),
            Domain.AgentManagement.Enums.AgentType.WritingAgent => domain.Contains("writing") || domain.Contains("documentation") || domain.Contains("content"),
            Domain.AgentManagement.Enums.AgentType.AnalysisAgent => domain.Contains("analysis") || domain.Contains("data") || domain.Contains("research"),
            Domain.AgentManagement.Enums.AgentType.PlanningAgent => domain.Contains("planning") || domain.Contains("management") || domain.Contains("strategy"),
            Domain.AgentManagement.Enums.AgentType.ResearchAgent => domain.Contains("research") || domain.Contains("investigation") || domain.Contains("information"),
            Domain.AgentManagement.Enums.AgentType.AutomationAgent => domain.Contains("automation") || domain.Contains("workflow") || domain.Contains("process"),
            _ => false
        };
    }

    private static double GetPriorityMultiplier(TaskPriority priority)
    {
        return priority switch
        {
            TaskPriority.Critical => 1.3,
            TaskPriority.High => 1.1,
            TaskPriority.Medium => 1.0,
            TaskPriority.Low => 0.9,
            _ => 1.0
        };
    }

    private static MatchReason DeterminePrimaryMatchReason(double relevanceScore, 
        List<string> matchedCapabilities, List<string> matchedSpecializations)
    {
        if (relevanceScore > 80 && matchedSpecializations.Any())
            return MatchReason.PerfectMatch;
        
        if (matchedSpecializations.Any())
            return MatchReason.SpecializationMatch;
        
        if (matchedCapabilities.Any())
            return MatchReason.CapabilityMatch;
        
        if (relevanceScore > 50)
            return MatchReason.TypeMatch;
        
        if (relevanceScore > 0)
            return MatchReason.BestAvailable;
        
        return MatchReason.FallbackOption;
    }

    private static double CalculateCapabilityStrength(AIAgent agent, string capability)
    {
        // This would ideally be based on historical performance data
        // For now, we'll use a simple heuristic based on specializations and success rate
        var relevantSpecs = agent.Specializations
            .Where(s => s.Keywords.Any(k => k.Contains(capability, StringComparison.OrdinalIgnoreCase)))
            .ToList();

        if (relevantSpecs.Any())
        {
            var avgSkill = relevantSpecs.Average(s => s.SkillLevel);
            var avgConfidence = relevantSpecs.Average(s => s.Confidence);
            return (avgSkill / 10.0) * avgConfidence * agent.GetSuccessRate();
        }

        // Base strength for agents with the capability but no specific specialization
        return agent.GetSuccessRate() * 0.5;
    }

    private static TimeSpan? EstimateTimeUntilFree(AIAgent agent)
    {
        if (agent.CanAcceptTask())
        {
            return TimeSpan.Zero;
        }

        // Estimate based on average response time and current tasks
        // This is a simplified estimation
        var avgTaskTime = TimeSpan.FromMilliseconds(agent.AverageResponseTime);
        var tasksRemaining = agent.CurrentTasks.Count;
        
        return TimeSpan.FromMilliseconds(avgTaskTime.TotalMilliseconds * tasksRemaining);
    }

    private static TeamRole DetermineTeamRole(AIAgent agent, IEnumerable<TaskComponent> assignedComponents)
    {
        var componentCount = assignedComponents.Count();
        var avgSkillLevel = agent.GetAverageSkillLevel();
        var successRate = agent.GetSuccessRate();

        if (componentCount > 2 || (avgSkillLevel >= 8 && successRate > 0.9))
            return TeamRole.Lead;
        
        if (avgSkillLevel >= 7 || agent.Specializations.Any())
            return TeamRole.Specialist;
        
        if (successRate > 0.8)
            return TeamRole.Support;
        
        return TeamRole.Support;
    }

    private static string GenerateTeamRationale(IEnumerable<AgentTeamMember> teamMembers, ComplexTaskRequirements requirements)
    {
        var memberCount = teamMembers.Count();
        var avgScore = teamMembers.Average(tm => tm.ContributionScore);
        var specialistCount = teamMembers.Count(tm => tm.Role == TeamRole.Specialist);
        
        return $"Team of {memberCount} agents with average capability score of {avgScore:F1}. " +
               $"Includes {specialistCount} specialists. " +
               $"{'P' + (requirements.AllowParallelExecution ? "arallel" : "Sequential")} execution planned.";
    }

    private static TimeSpan EstimateTeamExecutionTime(ComplexTaskRequirements requirements, IEnumerable<AgentTeamMember> teamMembers)
    {
        if (!teamMembers.Any())
            return TimeSpan.Zero;

        var avgResponseTime = teamMembers.Average(tm => tm.Agent.AverageResponseTime);
        var componentCount = requirements.Components.Count();
        
        if (requirements.AllowParallelExecution)
        {
            // Parallel execution time is roughly the longest individual component time
            return TimeSpan.FromMilliseconds(avgResponseTime * 1.5); // Add coordination overhead
        }
        else
        {
            // Sequential execution time is sum of all components
            return TimeSpan.FromMilliseconds(avgResponseTime * componentCount);
        }
    }

    private static IEnumerable<string> IdentifyTeamRisks(IEnumerable<AgentTeamMember> teamMembers, ComplexTaskRequirements requirements)
    {
        var risks = new List<string>();
        
        if (!teamMembers.Any())
        {
            risks.Add("No suitable agents found for task components");
        }

        var lowPerformanceAgents = teamMembers.Where(tm => tm.Agent.GetSuccessRate() < 0.7).Count();
        if (lowPerformanceAgents > 0)
        {
            risks.Add($"{lowPerformanceAgents} agent(s) have success rates below 70%");
        }

        var overloadedAgents = teamMembers.Where(tm => tm.AssignedComponents.Count() > 2).Count();
        if (overloadedAgents > 0)
        {
            risks.Add($"{overloadedAgents} agent(s) assigned to multiple components");
        }

        if (requirements.MaxExecutionTime.HasValue)
        {
            var estimatedTime = EstimateTeamExecutionTime(requirements, teamMembers);
            if (estimatedTime > requirements.MaxExecutionTime.Value)
            {
                risks.Add($"Estimated execution time ({estimatedTime}) exceeds maximum ({requirements.MaxExecutionTime.Value})");
            }
        }

        return risks;
    }
}