using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Domain.AgentManagement.ValueObjects;
using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentTrainingSystem(
    IAgentRepository agentRepository,
    ILogger<AgentTrainingSystem> logger) : IAgentTrainingSystem
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly ILogger<AgentTrainingSystem> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    // In-memory storage for performance data - in production, this would be persisted
    private readonly ConcurrentDictionary<Guid, List<TaskPerformanceRecord>> _performanceRecords = new();
    private readonly ConcurrentDictionary<Guid, List<UserFeedbackRecord>> _feedbackRecords = new();
    private readonly ConcurrentDictionary<Guid, List<LearningExample>> _learningExamples = new();
    private readonly ConcurrentDictionary<Guid, TrainingProgram> _trainingPrograms = new();
    private readonly ConcurrentDictionary<(Guid AgentId, Guid ProgramId), TrainingProgress> _trainingProgress = new();

    public Task<bool> RecordTaskPerformanceAsync(Guid agentId, TaskPerformanceRecord record, CancellationToken cancellationToken = default)
    {
        try
        {
            var records = _performanceRecords.GetOrAdd(agentId, _ => new List<TaskPerformanceRecord>());
            var recordWithTimestamp = record with { Timestamp = record.Timestamp == default ? DateTime.UtcNow : record.Timestamp };
            
            lock (records)
            {
                records.Add(recordWithTimestamp);
                // Keep only the last 1000 records per agent
                if (records.Count > 1000)
                {
                    records.RemoveAt(0);
                }
            }

            _logger.LogDebug("Recorded task performance for agent {AgentId}: Task {TaskId}, Success: {Success}, Quality: {Quality}", 
                agentId, record.TaskId, record.Success, record.QualityScore);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to record task performance for agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public Task<bool> RecordUserFeedbackAsync(Guid agentId, UserFeedbackRecord feedback, CancellationToken cancellationToken = default)
    {
        try
        {
            var records = _feedbackRecords.GetOrAdd(agentId, _ => new List<UserFeedbackRecord>());
            var feedbackWithTimestamp = feedback with { Timestamp = feedback.Timestamp == default ? DateTime.UtcNow : feedback.Timestamp };
            
            lock (records)
            {
                records.Add(feedbackWithTimestamp);
                // Keep only the last 500 feedback records per agent
                if (records.Count > 500)
                {
                    records.RemoveAt(0);
                }
            }

            _logger.LogInformation("Recorded user feedback for agent {AgentId}: Rating {Rating}, Category {Category}", 
                agentId, feedback.Rating, feedback.Category);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to record user feedback for agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public async Task<AgentPerformanceAnalysis> AnalyzeAgentPerformanceAsync(Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                throw new ArgumentException($"Agent {agentId} not found");
            }

            var analysisPeriod = period ?? TimeSpan.FromDays(30);
            var cutoffDate = DateTime.UtcNow.Subtract(analysisPeriod);

            var performanceRecords = _performanceRecords.GetValueOrDefault(agentId, new List<TaskPerformanceRecord>())
                .Where(r => r.Timestamp >= cutoffDate)
                .ToList();

            var feedbackRecords = _feedbackRecords.GetValueOrDefault(agentId, new List<UserFeedbackRecord>())
                .Where(r => r.Timestamp >= cutoffDate)
                .ToList();

            var overallMetrics = CalculateOverallMetrics(performanceRecords, feedbackRecords);
            var domainPerformance = CalculateDomainPerformance(performanceRecords, agent);
            var trends = CalculatePerformanceTrends(performanceRecords);
            var strengths = IdentifyStrengths(performanceRecords, feedbackRecords, agent);
            var weakAreas = IdentifyWeakAreas(performanceRecords, feedbackRecords, agent);
            var learningVelocity = CalculateLearningVelocity(performanceRecords);

            return new AgentPerformanceAnalysis(
                agentId,
                agent.Name,
                analysisPeriod,
                overallMetrics,
                domainPerformance,
                trends,
                strengths,
                weakAreas,
                learningVelocity
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze performance for agent {AgentId}", agentId);
            throw;
        }
    }

    public async Task<IEnumerable<PerformanceTrend>> GetPerformanceTrendsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var performanceRecords = _performanceRecords.GetValueOrDefault(agentId, new List<TaskPerformanceRecord>())
                .Where(r => r.Timestamp >= DateTime.UtcNow.AddDays(-90))
                .OrderBy(r => r.Timestamp)
                .ToList();

            return CalculatePerformanceTrends(performanceRecords);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get performance trends for agent {AgentId}", agentId);
            return Enumerable.Empty<PerformanceTrend>();
        }
    }

    public async Task<bool> UpdateAgentFromPerformanceAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for performance update", agentId);
                return false;
            }

            var analysis = await AnalyzeAgentPerformanceAsync(agentId, TimeSpan.FromDays(30), cancellationToken);
            
            // Update specializations based on performance
            foreach (var domainPerf in analysis.ByDomain)
            {
                var specialization = agent.GetSpecialization(domainPerf.Domain, domainPerf.Subdomain);
                if (specialization != null)
                {
                    // Adjust confidence based on recent performance
                    var confidenceAdjustment = (domainPerf.Metrics.SuccessRate - 0.7) * 0.1; // Adjust around 70% baseline
                    var newConfidence = Math.Max(0.0, Math.Min(1.0, specialization.Confidence + confidenceAdjustment));
                    
                    if (Math.Abs(newConfidence - specialization.Confidence) > 0.01)
                    {
                        var updatedSpecialization = specialization.UpdateConfidence(newConfidence);
                        agent.AddSpecialization(updatedSpecialization);
                    }
                }
            }

            await _agentRepository.UpdateAsync(agent, cancellationToken);
            
            _logger.LogInformation("Updated agent {AgentId} from performance analysis", agentId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update agent {AgentId} from performance", agentId);
            return false;
        }
    }

    public async Task<bool> LearnFromTaskOutcomeAsync(Guid agentId, TaskOutcome outcome, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for task outcome learning", agentId);
                return false;
            }

            // Record the outcome as a learning example
            var learningExample = new LearningExample(
                Guid.NewGuid(),
                outcome.Domain,
                outcome.TaskType,
                $"Task: {outcome.TaskType}",
                $"Duration: {outcome.Duration}, Confidence: {outcome.Confidence:P}",
                string.Join(", ", outcome.SkillsUsed),
                outcome.Success ? "Success" : "Failure",
                outcome.Success ? 1.0 : 0.0,
                outcome.SkillsUsed,
                DateTime.UtcNow
            );

            await AddLearningExampleAsync(agentId, learningExample, cancellationToken);

            // Update specialization usage
            agent.RecordSpecializationUsage(outcome.Domain, outcome.TaskType);
            
            await _agentRepository.UpdateAsync(agent, cancellationToken);

            _logger.LogInformation("Agent {AgentId} learned from task outcome: {TaskType} in {Domain} - {Result}", 
                agentId, outcome.TaskType, outcome.Domain, outcome.Success ? "Success" : "Failure");
            
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process task outcome learning for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> AdaptSpecializationsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for specialization adaptation", agentId);
                return false;
            }

            var analysis = await AnalyzeAgentPerformanceAsync(agentId, TimeSpan.FromDays(60), cancellationToken);
            
            // Identify underperforming specializations
            var underperformingDomains = analysis.ByDomain
                .Where(d => d.Metrics.SuccessRate < 0.6 && d.Metrics.TotalTasks > 5)
                .ToList();

            // Identify high-performing areas that could become new specializations
            var performanceRecords = _performanceRecords.GetValueOrDefault(agentId, new List<TaskPerformanceRecord>())
                .Where(r => r.Timestamp >= DateTime.UtcNow.AddDays(-60))
                .ToList();

            var emergingDomains = performanceRecords
                .Where(r => r.Success && r.QualityScore > 0.8)
                .GroupBy(r => new { r.Domain, r.Subdomain })
                .Where(g => g.Count() >= 3 && !agent.HasSpecializationIn(g.Key.Domain))
                .Select(g => new { g.Key.Domain, g.Key.Subdomain, Count = g.Count(), AvgQuality = g.Average(r => r.QualityScore) })
                .ToList();

            var hasChanges = false;

            // Add new specializations for emerging domains
            foreach (var emerging in emergingDomains)
            {
                if (emerging.Subdomain != null)
                {
                    var newSpec = AgentSpecialization.Create(
                        emerging.Domain,
                        emerging.Subdomain,
                        Math.Min(7, 3 + emerging.Count / 2), // Skill level based on experience
                        Math.Min(0.9, emerging.AvgQuality * 0.9) // Confidence based on quality
                    );
                    
                    agent.AddSpecialization(newSpec);
                    hasChanges = true;
                    
                    _logger.LogInformation("Added new specialization for agent {AgentId}: {Domain}/{Subdomain}", 
                        agentId, emerging.Domain, emerging.Subdomain);
                }
            }

            // Reduce confidence in underperforming specializations
            foreach (var underperforming in underperformingDomains)
            {
                var spec = agent.GetSpecialization(underperforming.Domain, underperforming.Subdomain);
                if (spec != null)
                {
                    var newConfidence = Math.Max(0.1, spec.Confidence - 0.1);
                    var updatedSpec = spec.UpdateConfidence(newConfidence);
                    agent.AddSpecialization(updatedSpec);
                    hasChanges = true;
                    
                    _logger.LogInformation("Reduced confidence for agent {AgentId} specialization: {Domain}/{Subdomain} to {Confidence:P}", 
                        agentId, underperforming.Domain, underperforming.Subdomain, newConfidence);
                }
            }

            if (hasChanges)
            {
                await _agentRepository.UpdateAsync(agent, cancellationToken);
            }

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to adapt specializations for agent {AgentId}", agentId);
            return false;
        }
    }

    public async Task<bool> RefineCapabilitiesAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for capability refinement", agentId);
                return false;
            }

            var performanceRecords = _performanceRecords.GetValueOrDefault(agentId, new List<TaskPerformanceRecord>())
                .Where(r => r.Timestamp >= DateTime.UtcNow.AddDays(-30))
                .ToList();

            // Find capabilities that are frequently used and successful
            var capabilityUsage = new Dictionary<string, (int successful, int total)>();
            
            foreach (var record in performanceRecords)
            {
                foreach (var keyword in record.Keywords)
                {
                    if (!capabilityUsage.ContainsKey(keyword))
                        capabilityUsage[keyword] = (0, 0);
                    
                    var current = capabilityUsage[keyword];
                    capabilityUsage[keyword] = (
                        record.Success ? current.successful + 1 : current.successful,
                        current.total + 1
                    );
                }
            }

            // Add capabilities for keywords that are frequently successful
            var newCapabilities = capabilityUsage
                .Where(kvp => kvp.Value.total >= 5 && (double)kvp.Value.successful / kvp.Value.total > 0.8)
                .Where(kvp => !agent.HasCapability(kvp.Key))
                .Select(kvp => kvp.Key)
                .ToList();

            foreach (var capability in newCapabilities)
            {
                agent.AddCapability(capability);
                _logger.LogInformation("Added capability '{Capability}' to agent {AgentId} based on performance", 
                    capability, agentId);
            }

            // Remove capabilities that are rarely used or unsuccessful
            var capabilitiesToRemove = agent.Capabilities
                .Where(cap => capabilityUsage.ContainsKey(cap))
                .Where(cap => capabilityUsage[cap].total >= 5 && (double)capabilityUsage[cap].successful / capabilityUsage[cap].total < 0.3)
                .ToList();

            foreach (var capability in capabilitiesToRemove)
            {
                agent.RemoveCapability(capability);
                _logger.LogInformation("Removed capability '{Capability}' from agent {AgentId} due to poor performance", 
                    capability, agentId);
            }

            if (newCapabilities.Any() || capabilitiesToRemove.Any())
            {
                await _agentRepository.UpdateAsync(agent, cancellationToken);
            }

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to refine capabilities for agent {AgentId}", agentId);
            return false;
        }
    }

    public Task<bool> AddLearningExampleAsync(Guid agentId, LearningExample example, CancellationToken cancellationToken = default)
    {
        try
        {
            var examples = _learningExamples.GetOrAdd(agentId, _ => new List<LearningExample>());
            var exampleWithTimestamp = example with { CreatedAt = example.CreatedAt == default ? DateTime.UtcNow : example.CreatedAt };
            
            lock (examples)
            {
                examples.Add(exampleWithTimestamp);
                // Keep only the last 200 examples per agent
                if (examples.Count > 200)
                {
                    examples.RemoveAt(0);
                }
            }

            _logger.LogDebug("Added learning example for agent {AgentId} in domain {Domain}/{Subdomain}", 
                agentId, example.Domain, example.Subdomain);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to add learning example for agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public Task<IEnumerable<LearningExample>> GetRelevantExamplesAsync(Guid agentId, string domain, string? subdomain = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var examples = _learningExamples.GetValueOrDefault(agentId, new List<LearningExample>());
            
            var relevantExamples = examples
                .Where(e => e.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase))
                .Where(e => subdomain == null || e.Subdomain.Equals(subdomain, StringComparison.OrdinalIgnoreCase))
                .OrderByDescending(e => e.Effectiveness)
                .ThenByDescending(e => e.CreatedAt)
                .Take(10)
                .ToList();

            return Task.FromResult<IEnumerable<LearningExample>>(relevantExamples);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get relevant examples for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<LearningExample>>(Enumerable.Empty<LearningExample>());
        }
    }

    public Task<bool> UpdateKnowledgeBaseAsync(Guid agentId, KnowledgeUpdate update, CancellationToken cancellationToken = default)
    {
        try
        {
            // In a real implementation, this would update a persistent knowledge base
            // For now, we'll add it as a learning example
            var example = new LearningExample(
                Guid.NewGuid(),
                update.Domain,
                update.Type,
                $"Knowledge Update: {update.Type}",
                update.Content,
                "Applied knowledge update",
                "Knowledge enhanced",
                update.Confidence,
                new[] { update.Type, update.Domain },
                update.ValidFrom == default ? DateTime.UtcNow : update.ValidFrom
            );

            return AddLearningExampleAsync(agentId, example, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update knowledge base for agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public Task<Guid> CreateTrainingProgramAsync(TrainingProgram program, CancellationToken cancellationToken = default)
    {
        try
        {
            _trainingPrograms[program.Id] = program;
            
            _logger.LogInformation("Created training program '{ProgramName}' with {ModuleCount} modules", 
                program.Name, program.Modules.Count());

            return Task.FromResult(program.Id);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create training program");
            throw;
        }
    }

    public async Task<bool> EnrollAgentInProgramAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for training enrollment", agentId);
                return false;
            }

            if (!_trainingPrograms.TryGetValue(programId, out var program))
            {
                _logger.LogWarning("Training program {ProgramId} not found", programId);
                return false;
            }

            var progress = new TrainingProgress(
                agentId,
                programId,
                TrainingStatus.NotStarted,
                0.0,
                program.Modules.Select(m => new ModuleProgress(
                    m.Id,
                    TrainingStatus.NotStarted,
                    0.0,
                    Enumerable.Empty<ExerciseResult>(),
                    DateTime.UtcNow
                )),
                0.0,
                DateTime.UtcNow
            );

            _trainingProgress[(agentId, programId)] = progress;

            _logger.LogInformation("Enrolled agent {AgentId} in training program '{ProgramName}'", 
                agentId, program.Name);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to enroll agent {AgentId} in program {ProgramId}", agentId, programId);
            return false;
        }
    }

    public async Task<bool> ExecuteTrainingAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_trainingProgress.TryGetValue((agentId, programId), out var progress))
            {
                _logger.LogWarning("No training progress found for agent {AgentId} in program {ProgramId}", agentId, programId);
                return false;
            }

            if (!_trainingPrograms.TryGetValue(programId, out var program))
            {
                _logger.LogWarning("Training program {ProgramId} not found", programId);
                return false;
            }

            // Simulate training execution
            var updatedProgress = progress with 
            { 
                Status = TrainingStatus.InProgress,
                CompletionPercentage = 100.0,
                OverallScore = 85.0, // Simulated score
                CompletedAt = DateTime.UtcNow
            };

            _trainingProgress[(agentId, programId)] = updatedProgress;

            _logger.LogInformation("Completed training for agent {AgentId} in program '{ProgramName}' with score {Score}", 
                agentId, program.Name, updatedProgress.OverallScore);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute training for agent {AgentId} in program {ProgramId}", agentId, programId);
            return false;
        }
    }

    public Task<TrainingProgress?> GetTrainingProgressAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default)
    {
        _trainingProgress.TryGetValue((agentId, programId), out var progress);
        return Task.FromResult(progress);
    }

    public async Task<AgentComparison> CompareAgentsAsync(IEnumerable<Guid> agentIds, string? domain = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var rankings = new List<AgentPerformanceRanking>();
            var insights = new List<ComparisonInsight>();

            foreach (var agentId in agentIds)
            {
                var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
                if (agent == null) continue;

                var analysis = await AnalyzeAgentPerformanceAsync(agentId, TimeSpan.FromDays(30), cancellationToken);
                
                var relevantPerformance = domain != null 
                    ? analysis.ByDomain.FirstOrDefault(d => d.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase))?.Metrics
                    : analysis.Overall;

                if (relevantPerformance != null)
                {
                    var score = CalculateComparisonScore(relevantPerformance);
                    rankings.Add(new AgentPerformanceRanking(
                        agentId,
                        agent.Name,
                        0, // Will be set after sorting
                        score,
                        analysis.Strengths,
                        analysis.WeakAreas
                    ));
                }
            }

            // Assign ranks
            var rankedAgents = rankings
                .OrderByDescending(r => r.Score)
                .Select((r, index) => r with { Rank = index + 1 })
                .ToList();

            return new AgentComparison(
                rankedAgents,
                domain ?? "Overall",
                DateTime.UtcNow,
                insights
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to compare agents");
            throw;
        }
    }

    public async Task<IEnumerable<AIAgent>> GetTopPerformingAgentsAsync(string? domain = null, int count = 10, CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var agentScores = new List<(AIAgent Agent, double Score)>();

            foreach (var agent in allAgents)
            {
                var analysis = await AnalyzeAgentPerformanceAsync(agent.Id, TimeSpan.FromDays(30), cancellationToken);
                
                var relevantPerformance = domain != null 
                    ? analysis.ByDomain.FirstOrDefault(d => d.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase))?.Metrics
                    : analysis.Overall;

                if (relevantPerformance != null)
                {
                    var score = CalculateComparisonScore(relevantPerformance);
                    agentScores.Add((agent, score));
                }
            }

            return agentScores
                .OrderByDescending(a => a.Score)
                .Take(count)
                .Select(a => a.Agent);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get top performing agents");
            return Enumerable.Empty<AIAgent>();
        }
    }

    public Task<BenchmarkResults> BenchmarkAgentAsync(Guid agentId, BenchmarkSuite suite, CancellationToken cancellationToken = default)
    {
        // Simplified benchmark implementation
        var results = suite.Tests.Select(test => new BenchmarkTestResult(
            test.Name,
            test.Category,
            true, // Simulated pass
            0.85, // Simulated score
            TimeSpan.FromSeconds(2),
            "Benchmark completed successfully"
        ));

        var overallScore = results.Average(r => r.Score);

        return Task.FromResult(new BenchmarkResults(
            agentId,
            suite.Name,
            overallScore,
            results,
            TimeSpan.FromMinutes(5),
            DateTime.UtcNow
        ));
    }

    public async Task OptimizeAllAgentsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var allAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            
            foreach (var agent in allAgents)
            {
                await UpdateAgentFromPerformanceAsync(agent.Id, cancellationToken);
                await AdaptSpecializationsAsync(agent.Id, cancellationToken);
                await RefineCapabilitiesAsync(agent.Id, cancellationToken);
            }

            _logger.LogInformation("Completed optimization for {AgentCount} agents", allAgents.Count());
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to optimize all agents");
        }
    }

    public async Task<OptimizationSuggestion> AnalyzeOptimizationOpportunitiesAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var analysis = await AnalyzeAgentPerformanceAsync(agentId, TimeSpan.FromDays(30), cancellationToken);
            var actions = new List<OptimizationAction>();

            // Suggest improvements based on weak areas
            foreach (var weakArea in analysis.WeakAreas.Take(3))
            {
                actions.Add(new OptimizationAction(
                    OptimizationActionType.AddTraining,
                    new Dictionary<string, object> { ["domain"] = weakArea, ["type"] = "remedial" },
                    0.8
                ));
            }

            // Suggest specialization adjustments for underperforming domains
            var underperformingDomains = analysis.ByDomain
                .Where(d => d.Metrics.SuccessRate < 0.7)
                .Take(2);

            foreach (var domain in underperformingDomains)
            {
                actions.Add(new OptimizationAction(
                    OptimizationActionType.UpdateSpecialization,
                    new Dictionary<string, object> 
                    { 
                        ["domain"] = domain.Domain,
                        ["subdomain"] = domain.Subdomain ?? "",
                        ["action"] = "reduce_confidence"
                    },
                    0.6
                ));
            }

            var expectedImprovement = actions.Sum(a => a.Priority * 0.1);

            return new OptimizationSuggestion(
                agentId,
                OptimizationType.PerformanceOptimization,
                $"Optimize performance in {analysis.WeakAreas.Count()} weak areas",
                expectedImprovement,
                actions,
                0.75,
                "Based on recent performance analysis"
            );
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze optimization opportunities for agent {AgentId}", agentId);
            throw;
        }
    }

    public async Task<bool> ApplyOptimizationAsync(Guid agentId, OptimizationSuggestion suggestion, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for optimization", agentId);
                return false;
            }

            foreach (var action in suggestion.Actions)
            {
                switch (action.Type)
                {
                    case OptimizationActionType.UpdateSpecialization:
                        await ApplySpecializationOptimization(agent, action, cancellationToken);
                        break;
                    case OptimizationActionType.AddCapability:
                        await ApplyCapabilityOptimization(agent, action, cancellationToken);
                        break;
                    case OptimizationActionType.AddTraining:
                        await ApplyTrainingOptimization(agentId, action, cancellationToken);
                        break;
                }
            }

            await _agentRepository.UpdateAsync(agent, cancellationToken);

            _logger.LogInformation("Applied optimization suggestion for agent {AgentId}: {Description}", 
                agentId, suggestion.Description);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to apply optimization for agent {AgentId}", agentId);
            return false;
        }
    }

    // Helper methods for calculations
    private PerformanceMetrics CalculateOverallMetrics(List<TaskPerformanceRecord> performanceRecords, List<UserFeedbackRecord> feedbackRecords)
    {
        if (!performanceRecords.Any())
        {
            return new PerformanceMetrics(0, 0.0, 0.0, TimeSpan.Zero, 0.0, 0, 0);
        }

        var totalTasks = performanceRecords.Count;
        var successfulTasks = performanceRecords.Count(r => r.Success);
        var successRate = (double)successfulTasks / totalTasks;
        var avgQuality = performanceRecords.Average(r => r.QualityScore);
        var avgTime = TimeSpan.FromMilliseconds(performanceRecords.Average(r => r.ExecutionTime.TotalMilliseconds));
        var userSatisfaction = feedbackRecords.Any() ? feedbackRecords.Average(f => f.Rating) / 5.0 : 0.0;

        return new PerformanceMetrics(
            totalTasks,
            successRate,
            avgQuality,
            avgTime,
            userSatisfaction,
            0, // Would need historical data for improvement count
            0  // Would need historical data for regression count
        );
    }

    private IEnumerable<DomainPerformance> CalculateDomainPerformance(List<TaskPerformanceRecord> performanceRecords, AIAgent agent)
    {
        return performanceRecords
            .GroupBy(r => new { r.Domain, r.Subdomain })
            .Select(g =>
            {
                var records = g.ToList();
                var metrics = CalculateOverallMetrics(records, new List<UserFeedbackRecord>());
                var specialization = agent.GetSpecialization(g.Key.Domain, g.Key.Subdomain);
                var specializationLevel = specialization?.SkillLevel ?? 0;
                var topSkills = records.SelectMany(r => r.Keywords).GroupBy(k => k).OrderByDescending(kg => kg.Count()).Take(3).Select(kg => kg.Key);

                return new DomainPerformance(
                    g.Key.Domain,
                    g.Key.Subdomain,
                    metrics,
                    specializationLevel,
                    topSkills
                );
            });
    }

    private IEnumerable<PerformanceTrend> CalculatePerformanceTrends(List<TaskPerformanceRecord> performanceRecords)
    {
        var trends = new List<PerformanceTrend>();

        if (performanceRecords.Count < 10) return trends;

        var recentRecords = performanceRecords.TakeLast(performanceRecords.Count / 2);
        var olderRecords = performanceRecords.Take(performanceRecords.Count / 2);

        var recentSuccessRate = recentRecords.Count(r => r.Success) / (double)recentRecords.Count();
        var olderSuccessRate = olderRecords.Count(r => r.Success) / (double)olderRecords.Count();
        var successRateChange = (recentSuccessRate - olderSuccessRate) / olderSuccessRate;

        trends.Add(new PerformanceTrend(
            "Success Rate",
            successRateChange > 0.05 ? "Improving" : successRateChange < -0.05 ? "Declining" : "Stable",
            successRateChange * 100,
            TimeSpan.FromDays(30)
        ));

        return trends;
    }

    private IEnumerable<string> IdentifyStrengths(List<TaskPerformanceRecord> performanceRecords, List<UserFeedbackRecord> feedbackRecords, AIAgent agent)
    {
        var strengths = new List<string>();

        // High success rate
        if (performanceRecords.Any() && performanceRecords.Count(r => r.Success) / (double)performanceRecords.Count > 0.8)
        {
            strengths.Add("High task success rate");
        }

        // Fast execution
        if (performanceRecords.Any() && performanceRecords.Average(r => r.ExecutionTime.TotalMinutes) < 2)
        {
            strengths.Add("Fast task execution");
        }

        // High quality output
        if (performanceRecords.Any() && performanceRecords.Average(r => r.QualityScore) > 0.8)
        {
            strengths.Add("High quality output");
        }

        // Positive user feedback
        if (feedbackRecords.Any() && feedbackRecords.Average(f => f.Rating) > 4)
        {
            strengths.Add("Excellent user satisfaction");
        }

        return strengths;
    }

    private IEnumerable<string> IdentifyWeakAreas(List<TaskPerformanceRecord> performanceRecords, List<UserFeedbackRecord> feedbackRecords, AIAgent agent)
    {
        var weakAreas = new List<string>();

        // Low success rate
        if (performanceRecords.Any() && performanceRecords.Count(r => r.Success) / (double)performanceRecords.Count < 0.6)
        {
            weakAreas.Add("Low task success rate");
        }

        // Slow execution
        if (performanceRecords.Any() && performanceRecords.Average(r => r.ExecutionTime.TotalMinutes) > 10)
        {
            weakAreas.Add("Slow task execution");
        }

        // Low quality output
        if (performanceRecords.Any() && performanceRecords.Average(r => r.QualityScore) < 0.6)
        {
            weakAreas.Add("Low quality output");
        }

        // Poor user feedback
        if (feedbackRecords.Any() && feedbackRecords.Average(f => f.Rating) < 3)
        {
            weakAreas.Add("Poor user satisfaction");
        }

        return weakAreas;
    }

    private double CalculateLearningVelocity(List<TaskPerformanceRecord> performanceRecords)
    {
        if (performanceRecords.Count < 20) return 0.0;

        // Simple learning velocity calculation based on improvement over time
        var chunks = performanceRecords.Chunk(5).ToList();
        if (chunks.Count < 2) return 0.0;

        var improvements = 0;
        for (int i = 1; i < chunks.Count; i++)
        {
            var prevSuccess = chunks[i - 1].Count(r => r.Success) / (double)chunks[i - 1].Length;
            var currentSuccess = chunks[i].Count(r => r.Success) / (double)chunks[i].Length;
            if (currentSuccess > prevSuccess) improvements++;
        }

        return improvements / (double)(chunks.Count - 1);
    }

    private double CalculateComparisonScore(PerformanceMetrics metrics)
    {
        return (metrics.SuccessRate * 40) + 
               (metrics.AverageQualityScore * 30) + 
               (metrics.UserSatisfactionScore * 20) + 
               (Math.Max(0, 10 - metrics.AverageExecutionTime.TotalMinutes) * 10);
    }

    private async Task ApplySpecializationOptimization(AIAgent agent, OptimizationAction action, CancellationToken cancellationToken)
    {
        if (action.Parameters.TryGetValue("domain", out var domainObj) && domainObj is string domain &&
            action.Parameters.TryGetValue("subdomain", out var subdomainObj) && subdomainObj is string subdomain)
        {
            var spec = agent.GetSpecialization(domain, subdomain);
            if (spec != null && action.Parameters.TryGetValue("action", out var actionObj) && actionObj is string actionType)
            {
                if (actionType == "reduce_confidence")
                {
                    var newConfidence = Math.Max(0.1, spec.Confidence - 0.1);
                    var updatedSpec = spec.UpdateConfidence(newConfidence);
                    agent.AddSpecialization(updatedSpec);
                }
            }
        }
    }

    private async Task ApplyCapabilityOptimization(AIAgent agent, OptimizationAction action, CancellationToken cancellationToken)
    {
        if (action.Parameters.TryGetValue("capability", out var capabilityObj) && capabilityObj is string capability)
        {
            agent.AddCapability(capability);
        }
    }

    private async Task ApplyTrainingOptimization(Guid agentId, OptimizationAction action, CancellationToken cancellationToken)
    {
        // In a real implementation, this would create and execute a training program
        _logger.LogInformation("Training optimization applied for agent {AgentId}: {Parameters}", 
            agentId, string.Join(", ", action.Parameters.Select(kvp => $"{kvp.Key}={kvp.Value}")));
    }
}