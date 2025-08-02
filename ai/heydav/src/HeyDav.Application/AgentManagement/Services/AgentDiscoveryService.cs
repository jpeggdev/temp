using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentDiscoveryService(
    IAgentRepository agentRepository,
    IAgentCapabilityMatcher capabilityMatcher,
    ILogger<AgentDiscoveryService> logger) : IAgentDiscoveryService
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentCapabilityMatcher _capabilityMatcher = capabilityMatcher ?? throw new ArgumentNullException(nameof(capabilityMatcher));
    private readonly ILogger<AgentDiscoveryService> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    public async Task<IEnumerable<AgentDiscoveryResult>> DiscoverAgentsAsync(AgentDiscoveryRequest request, CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var results = new List<AgentDiscoveryResult>();

            foreach (var agent in allAgents)
            {
                var result = await EvaluateAgentForDiscovery(agent, request);
                if (result.RelevanceScore > 0)
                {
                    results.Add(result);
                }
            }

            return results.OrderByDescending(r => r.RelevanceScore);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to discover agents");
            return Enumerable.Empty<AgentDiscoveryResult>();
        }
    }

    public async Task<IEnumerable<AIAgent>> FindSimilarAgentsAsync(Guid agentId, int maxResults = 5, CancellationToken cancellationToken = default)
    {
        try
        {
            var targetAgent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (targetAgent == null)
            {
                return Enumerable.Empty<AIAgent>();
            }

            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var similarAgents = allAgents
                .Where(a => a.Id != agentId)
                .Select(a => new { Agent = a, Similarity = CalculateSimilarity(targetAgent, a) })
                .Where(x => x.Similarity > 0.3) // Minimum similarity threshold
                .OrderByDescending(x => x.Similarity)
                .Take(maxResults)
                .Select(x => x.Agent);

            return similarAgents;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to find similar agents for {AgentId}", agentId);
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

    public async Task<IEnumerable<AgentRecommendation>> GetRecommendedAgentsAsync(TaskContext context, CancellationToken cancellationToken = default)
    {
        try
        {
            var taskRequirements = new TaskRequirements(
                context.Description,
                context.Domain,
                context.Category,
                context.RequiredSkills,
                context.RequiredSkills,
                context.Priority,
                context.EstimatedDuration
            );

            var matches = await _capabilityMatcher.FindMatchingAgentsAsync(taskRequirements, cancellationToken);
            
            return matches.Select(match => new AgentRecommendation(
                match.Agent,
                match.Score,
                DetermineRecommendationType(match),
                GenerateRationale(match, context),
                GenerateBenefits(match),
                GenerateConsiderations(match),
                match.Score / 100.0
            ));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get recommended agents for task context");
            return Enumerable.Empty<AgentRecommendation>();
        }
    }

    public async Task<IEnumerable<AgentRecommendation>> GetComplementaryAgentsAsync(Guid agentId, string objective, CancellationToken cancellationToken = default)
    {
        try
        {
            var primaryAgent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (primaryAgent == null)
            {
                return Enumerable.Empty<AgentRecommendation>();
            }

            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var complementaryAgents = allAgents
                .Where(a => a.Id != agentId)
                .Select(a => new {
                    Agent = a,
                    ComplementaryScore = CalculateComplementaryScore(primaryAgent, a, objective)
                })
                .Where(x => x.ComplementaryScore > 0.4)
                .OrderByDescending(x => x.ComplementaryScore)
                .Take(5)
                .Select(x => new AgentRecommendation(
                    x.Agent,
                    x.ComplementaryScore * 100,
                    RecommendationType.Complementary,
                    $"Complements {primaryAgent.Name} for {objective}",
                    new[] { "Different expertise", "Collaborative potential" },
                    new[] { "Coordination needed", "Potential conflicts" },
                    x.ComplementaryScore
                ));

            return complementaryAgents;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get complementary agents for {AgentId}", agentId);
            return Enumerable.Empty<AgentRecommendation>();
        }
    }

    public async Task<IEnumerable<TeamRecommendation>> RecommendTeamsForTaskAsync(ComplexTaskContext context, CancellationToken cancellationToken = default)
    {
        try
        {
            // Simplified team recommendation logic
            var allAgents = await _agentRepository.GetAvailableAgentsAsync(cancellationToken);
            var teams = new List<TeamRecommendation>();

            // Generate a basic team recommendation
            var selectedAgents = allAgents.Take(Math.Min(context.Components.Count(), 5)).ToList();
            
            if (selectedAgents.Count >= 2)
            {
                var teamMembers = selectedAgents.Select((agent, index) => new AgentTeamMemberRecommendation(
                    agent,
                    index == 0 ? TeamRole.Lead : TeamRole.Specialist,
                    new[] { $"Handle component {index + 1}" },
                    agent.GetSuccessRate() * 100,
                    Enumerable.Empty<Guid>()
                ));

                var teamScore = selectedAgents.Average(a => a.GetSuccessRate()) * 100;
                
                teams.Add(new TeamRecommendation(
                    teamMembers,
                    teamScore,
                    "Balanced team with complementary skills",
                    new[] { "Diverse expertise", "Good availability" },
                    new[] { "Coordination complexity" },
                    context.EstimatedDuration ?? TimeSpan.FromHours(8),
                    0.8 // Collaboration compatibility
                ));
            }

            return teams;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to recommend teams for complex task");
            return Enumerable.Empty<TeamRecommendation>();
        }
    }

    public async Task<CapabilityGapAnalysis> AnalyzeCapabilityGapsAsync(IEnumerable<string> requiredCapabilities, CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var allCapabilities = allAgents.SelectMany(a => a.Capabilities).Distinct().ToList();
            var requiredCapsList = requiredCapabilities.ToList();

            var missingCapabilities = requiredCapsList.Except(allCapabilities).ToList();
            var gaps = new List<CapabilityGap>();
            var strategies = new List<GapMitigationStrategy>();

            foreach (var capability in requiredCapsList)
            {
                var agentsWithCapability = allAgents.Where(a => a.HasCapability(capability)).ToList();
                var gap = new CapabilityGap(
                    capability,
                    3, // Ideally want 3 agents per capability
                    agentsWithCapability.Count,
                    agentsWithCapability.Any() ? agentsWithCapability.Average(a => a.GetAverageSkillLevel()) : 0,
                    agentsWithCapability.Count == 0 ? GapSeverity.Critical :
                    agentsWithCapability.Count == 1 ? GapSeverity.High :
                    agentsWithCapability.Count == 2 ? GapSeverity.Medium : GapSeverity.Low
                );
                gaps.Add(gap);

                if (gap.Severity != GapSeverity.Low)
                {
                    strategies.Add(new GapMitigationStrategy(
                        gap.Severity == GapSeverity.Critical ? "Train existing agents" : "Add specialized agents",
                        new[] { capability },
                        gap.Severity == GapSeverity.Critical ? 0.7 : 0.9,
                        TimeSpan.FromDays(gap.Severity == GapSeverity.Critical ? 30 : 14),
                        new[] { $"Identify agents for {capability} training", "Create training program" }
                    ));
                }
            }

            var coverageScore = requiredCapsList.Count > 0 ? 
                (double)(requiredCapsList.Count - missingCapabilities.Count) / requiredCapsList.Count : 1.0;

            return new CapabilityGapAnalysis(
                missingCapabilities,
                gaps.Where(g => g.AvailableAgents < 2).Select(g => g.Capability),
                gaps,
                strategies,
                coverageScore
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze capability gaps");
            throw;
        }
    }

    public async Task<CapabilityCoverage> GetCapabilityCoverageAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var allCapabilities = allAgents.SelectMany(a => a.Capabilities).Distinct().ToList();
            
            var coverageDetails = allCapabilities.Select(capability =>
            {
                var agentsWithCapability = allAgents.Where(a => a.HasCapability(capability)).ToList();
                var avgSkill = agentsWithCapability.Average(a => a.GetAverageSkillLevel());
                var avgConfidence = agentsWithCapability.Average(a => a.GetAverageConfidence());
                
                var quality = agentsWithCapability.Count >= 3 && avgSkill >= 7 ? CoverageQuality.Excellent :
                              agentsWithCapability.Count >= 2 && avgSkill >= 6 ? CoverageQuality.Good :
                              agentsWithCapability.Count >= 1 && avgSkill >= 5 ? CoverageQuality.Adequate :
                              agentsWithCapability.Count >= 1 ? CoverageQuality.Poor : CoverageQuality.Missing;

                return new CapabilityCoverageDetail(capability, agentsWithCapability.Count, avgSkill, avgConfidence, quality);
            }).ToList();

            var wellCovered = coverageDetails.Where(d => d.Quality == CoverageQuality.Excellent).Select(d => d.Capability);
            var poorlyCovered = coverageDetails.Where(d => d.Quality == CoverageQuality.Poor || d.Quality == CoverageQuality.Missing).Select(d => d.Capability);

            return new CapabilityCoverage(
                allCapabilities.Count,
                coverageDetails.Count(d => d.Quality != CoverageQuality.Missing),
                coverageDetails.Count > 0 ? (double)coverageDetails.Count(d => d.Quality != CoverageQuality.Missing) / coverageDetails.Count : 0,
                coverageDetails,
                wellCovered,
                poorlyCovered
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get capability coverage");
            throw;
        }
    }

    public async Task<IEnumerable<CapabilityCluster>> GetCapabilityClustersAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var clusters = new List<CapabilityCluster>();

            // Simple clustering based on agent types
            var agentsByType = allAgents.GroupBy(a => a.Type);
            
            foreach (var typeGroup in agentsByType)
            {
                var capabilities = typeGroup.SelectMany(a => a.Capabilities).Distinct().ToList();
                var strength = typeGroup.Average(a => a.GetSuccessRate());
                
                clusters.Add(new CapabilityCluster(
                    typeGroup.Key.ToString(),
                    capabilities,
                    typeGroup.Select(a => a.Id),
                    strength,
                    new[] { $"Type: {typeGroup.Key}" }
                ));
            }

            return clusters;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get capability clusters");
            return Enumerable.Empty<CapabilityCluster>();
        }
    }

    // Simplified implementations for the remaining methods
    public async Task<AgentNetwork> AnalyzeAgentNetworkAsync(CancellationToken cancellationToken = default)
    {
        var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
        var nodes = allAgents.Select(a => new AgentNetworkNode(
            a.Id, a.Name, 0, 0.5, a.Capabilities.Take(3)
        ));

        return new AgentNetwork(
            nodes,
            Enumerable.Empty<AgentNetworkEdge>(),
            new NetworkMetrics(allAgents.Count(), 0, 0, 0, 0, Enumerable.Empty<Guid>()),
            Enumerable.Empty<AgentCommunity>()
        );
    }

    public Task<IEnumerable<AgentInfluenceMetric>> GetAgentInfluenceMetricsAsync(CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<AgentInfluenceMetric>>(Enumerable.Empty<AgentInfluenceMetric>());
    }

    public Task<IEnumerable<CollaborationPattern>> GetCollaborationPatternsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<CollaborationPattern>>(Enumerable.Empty<CollaborationPattern>());
    }

    public async Task<IEnumerable<AgentAvailability>> GetAgentAvailabilityAsync(IEnumerable<Guid>? agentIds = null, CancellationToken cancellationToken = default)
    {
        var agents = agentIds != null ?
            await Task.WhenAll(agentIds.Select(id => _agentRepository.GetByIdAsync(id, cancellationToken))) :
            (await _agentRepository.GetActiveAgentsAsync(cancellationToken)).ToArray();

        return agents.Where(a => a != null).Select(a => new AgentAvailability(
            a!.Id,
            a.Name,
            a.CanAcceptTask() ? AvailabilityStatus.Immediately : AvailabilityStatus.Busy,
            a.CurrentTasks.Count,
            a.Configuration.MaxConcurrentTasks,
            a.CanAcceptTask() ? TimeSpan.Zero : TimeSpan.FromHours(1),
            a.CurrentTasks.Select(t => t.Title)
        ));
    }

    public async Task<LoadBalancingRecommendation> GetLoadBalancingRecommendationAsync(TaskRequirements task, CancellationToken cancellationToken = default)
    {
        var bestMatch = await _capabilityMatcher.FindBestMatchAsync(task, cancellationToken);
        var alternatives = await _capabilityMatcher.FindBackupAgentsAsync(task, 3, cancellationToken);

        return new LoadBalancingRecommendation(
            bestMatch?.Agent ?? throw new InvalidOperationException("No suitable agent found"),
            alternatives.Select(m => m.Agent),
            LoadDistributionStrategy.CapabilityBased,
            "Selected based on capability match and availability",
            0.2
        );
    }

    public async Task<IEnumerable<AIAgent>> GetUnderutilizedAgentsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
        return allAgents.Where(a => a.CurrentTasks.Count == 0 || 
                                   (double)a.CurrentTasks.Count / a.Configuration.MaxConcurrentTasks < 0.3);
    }

    public async Task<AgentProvisioningRecommendation> AnalyzeProvisioningNeedsAsync(CancellationToken cancellationToken = default)
    {
        var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
        var utilization = allAgents.Average(a => (double)a.CurrentTasks.Count / a.Configuration.MaxConcurrentTasks);

        return new AgentProvisioningRecommendation(
            Enumerable.Empty<AgentProvisioningNeed>(),
            Enumerable.Empty<AgentRetirementCandidate>(),
            Enumerable.Empty<AgentUpgradeOpportunity>(),
            utilization,
            utilization > 0.8 ? "High utilization - consider adding agents" : 
            utilization < 0.3 ? "Low utilization - consider retiring agents" : "Balanced utilization"
        );
    }

    public Task<IEnumerable<AgentScalingOpportunity>> GetScalingOpportunitiesAsync(CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<AgentScalingOpportunity>>(Enumerable.Empty<AgentScalingOpportunity>());
    }

    // Helper methods
    private async Task<AgentDiscoveryResult> EvaluateAgentForDiscovery(AIAgent agent, AgentDiscoveryRequest request)
    {
        var score = 0.0;
        var matchedCapabilities = new List<string>();
        var matchedSpecializations = new List<string>();
        var matchReasons = new List<string>();
        var detailedScores = new Dictionary<string, double>();

        // Capability matching
        if (request.RequiredCapabilities?.Any() == true)
        {
            var capabilityMatches = request.RequiredCapabilities.Count(c => agent.HasCapability(c));
            var capabilityScore = (double)capabilityMatches / request.RequiredCapabilities.Count() * 40;
            score += capabilityScore;
            detailedScores["Capabilities"] = capabilityScore;
            matchedCapabilities.AddRange(request.RequiredCapabilities.Where(c => agent.HasCapability(c)));
        }

        // Type matching
        if (request.PreferredType.HasValue && agent.Type == request.PreferredType.Value)
        {
            score += 20;
            detailedScores["Type"] = 20;
            matchReasons.Add("Preferred agent type");
        }

        // Performance matching
        if (request.MinSuccessRate.HasValue)
        {
            var successRate = agent.GetSuccessRate();
            if (successRate >= request.MinSuccessRate.Value)
            {
                score += 20;
                detailedScores["Performance"] = 20;
                matchReasons.Add("Meets success rate requirement");
            }
        }

        // Availability
        var availability = agent.CanAcceptTask() ? 
            (request.MustBeAvailable == true ? AvailabilityStatus.Immediately : AvailabilityStatus.Soon) :
            AvailabilityStatus.Busy;

        if (request.MustBeAvailable == true && !agent.CanAcceptTask())
        {
            score = 0; // Hard requirement
        }
        else if (agent.CanAcceptTask())
        {
            score += 20;
            detailedScores["Availability"] = 20;
            matchReasons.Add("Available for tasks");
        }

        var matchQuality = score >= 80 ? MatchQuality.Excellent :
                          score >= 60 ? MatchQuality.Good :
                          score >= 40 ? MatchQuality.Fair :
                          score > 0 ? MatchQuality.Poor : MatchQuality.NoMatch;

        return new AgentDiscoveryResult(
            agent,
            score,
            matchQuality,
            matchedCapabilities,
            matchedSpecializations,
            availability,
            matchReasons,
            detailedScores
        );
    }

    private static double CalculateSimilarity(AIAgent agent1, AIAgent agent2)
    {
        var score = 0.0;

        // Type similarity
        if (agent1.Type == agent2.Type)
            score += 30.0;

        // Capability overlap
        var commonCapabilities = agent1.Capabilities.Intersect(agent2.Capabilities).Count();
        var totalCapabilities = agent1.Capabilities.Union(agent2.Capabilities).Count();
        if (totalCapabilities > 0)
            score += (double)commonCapabilities / totalCapabilities * 40.0;

        // Specialization similarity
        var commonDomains = agent1.Specializations.Select(s => s.Domain)
            .Intersect(agent2.Specializations.Select(s => s.Domain)).Count();
        var totalDomains = agent1.Specializations.Select(s => s.Domain)
            .Union(agent2.Specializations.Select(s => s.Domain)).Count();
        if (totalDomains > 0)
            score += (double)commonDomains / totalDomains * 30.0;

        return Math.Min(100.0, score) / 100.0;
    }

    private static double CalculateComplementaryScore(AIAgent primaryAgent, AIAgent candidateAgent, string objective)
    {
        var score = 0.0;

        // Different specialization domains (complementary)
        var primaryDomains = primaryAgent.Specializations.Select(s => s.Domain).ToHashSet();
        var candidateDomains = candidateAgent.Specializations.Select(s => s.Domain).ToHashSet();
        
        var overlap = primaryDomains.Intersect(candidateDomains).Count();
        var union = primaryDomains.Union(candidateDomains).Count();
        
        // High complementary score for low overlap but good union coverage
        if (union > 0)
        {
            score += (1.0 - (double)overlap / union) * 50.0;
        }

        // Different capabilities
        var capabilityOverlap = primaryAgent.Capabilities.Intersect(candidateAgent.Capabilities).Count();
        var capabilityUnion = primaryAgent.Capabilities.Union(candidateAgent.Capabilities).Count();
        
        if (capabilityUnion > 0)
        {
            score += (1.0 - (double)capabilityOverlap / capabilityUnion) * 30.0;
        }

        // Performance compatibility
        var performanceDiff = Math.Abs(primaryAgent.GetSuccessRate() - candidateAgent.GetSuccessRate());
        score += Math.Max(0, 20.0 - performanceDiff * 40.0); // Penalty for large performance gaps

        return Math.Min(100.0, score) / 100.0;
    }

    private static RecommendationType DetermineRecommendationType(AgentMatch match)
    {
        return match.PrimaryReason switch
        {
            MatchReason.PerfectMatch => RecommendationType.BestMatch,
            MatchReason.SpecializationMatch => RecommendationType.Specialist,
            MatchReason.CapabilityMatch => RecommendationType.HighPerformer,
            _ => RecommendationType.Generalist
        };
    }

    private static string GenerateRationale(AgentMatch match, TaskContext context)
    {
        return $"Agent {match.Agent.Name} scored {match.Score:F1} for task '{context.Description}' " +
               $"based on {match.PrimaryReason.ToString().ToLowerInvariant()}";
    }

    private static IEnumerable<string> GenerateBenefits(AgentMatch match)
    {
        var benefits = new List<string>();
        
        if (match.PerformanceScore > 80)
            benefits.Add("High performance track record");
        
        if (match.AvailabilityScore > 90)
            benefits.Add("Immediately available");
        
        if (match.MatchedCapabilities.Any())
            benefits.Add($"Has {match.MatchedCapabilities.Count()} required capabilities");

        return benefits;
    }

    private static IEnumerable<string> GenerateConsiderations(AgentMatch match)
    {
        var considerations = new List<string>();

        if (match.Agent.CurrentTasks.Any())
            considerations.Add($"Currently handling {match.Agent.CurrentTasks.Count} tasks");

        if (match.Concerns.Any())
            considerations.AddRange(match.Concerns);

        return considerations;
    }
}