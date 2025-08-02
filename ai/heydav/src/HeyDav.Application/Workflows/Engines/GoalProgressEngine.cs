using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.TodoManagement.Interfaces;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Engines;

public class GoalProgressEngine : IGoalProgressEngine
{
    private readonly IGoalRepository _goalRepository;
    private readonly ITodoRepository _todoRepository;
    private readonly IHabitRepository _habitRepository;
    private readonly IGoalAnalytics _goalAnalytics;
    private readonly IActionPlanGenerator _actionPlanGenerator;
    private readonly IProgressPredictor _progressPredictor;

    public GoalProgressEngine(
        IGoalRepository goalRepository,
        ITodoRepository todoRepository,
        IHabitRepository habitRepository,
        IGoalAnalytics goalAnalytics,
        IActionPlanGenerator actionPlanGenerator,
        IProgressPredictor progressPredictor)
    {
        _goalRepository = goalRepository;
        _todoRepository = todoRepository;
        _habitRepository = habitRepository;
        _goalAnalytics = goalAnalytics;
        _actionPlanGenerator = actionPlanGenerator;
        _progressPredictor = progressPredictor;
    }

    public async Task<GoalProgressReport> GenerateProgressReportAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        var goal = await _goalRepository.GetByIdAsync(goalId, cancellationToken);
        if (goal == null)
            throw new ArgumentException($"Goal with ID {goalId} not found");

        var relatedTasks = await _todoRepository.GetByGoalIdAsync(goalId, cancellationToken);
        var relatedHabits = await _habitRepository.GetByUserIdAsync("", includeInactive: false, cancellationToken); // Would need user context
        var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goalId, cancellationToken);

        var report = new GoalProgressReport
        {
            GoalId = goalId,
            GoalTitle = goal.Title,
            CurrentProgress = goal.Progress,
            ProgressSinceLastWeek = CalculateWeeklyProgress(progressHistory),
            ProgressSinceLastMonth = CalculateMonthlyProgress(progressHistory),
            CompletedMilestones = goal.Milestones.Count(m => m.IsCompleted),
            TotalMilestones = goal.Milestones.Count,
            RelatedTasksCompleted = relatedTasks.Count(t => t.Status == TodoStatus.Completed),
            TotalRelatedTasks = relatedTasks.Count,
            EstimatedCompletionDate = await _progressPredictor.PredictCompletionDateAsync(goalId, cancellationToken),
            ProgressVelocity = CalculateProgressVelocity(progressHistory),
            StatusSummary = GenerateStatusSummary(goal, relatedTasks),
            Recommendations = await GenerateRecommendationsAsync(goal, relatedTasks, progressHistory, cancellationToken),
            NextMilestones = GetNextMilestones(goal),
            BlockersAndRisks = await IdentifyBlockersAndRisksAsync(goal, relatedTasks, cancellationToken)
        };

        return report;
    }

    public async Task<List<ActionItem>> GenerateActionPlanAsync(Guid goalId, ActionPlanRequest request, CancellationToken cancellationToken = default)
    {
        var goal = await _goalRepository.GetByIdAsync(goalId, cancellationToken);
        if (goal == null)
            throw new ArgumentException($"Goal with ID {goalId} not found");

        var context = new ActionPlanContext
        {
            Goal = goal,
            Timeframe = request.Timeframe,
            AvailableTimePerWeek = request.AvailableTimePerWeek,
            PriorityLevel = request.PriorityLevel,
            SkillLevel = request.SkillLevel,
            Resources = request.AvailableResources,
            Constraints = request.Constraints
        };

        var actionItems = await _actionPlanGenerator.GenerateActionItemsAsync(context, cancellationToken);

        // Prioritize and sequence action items
        var prioritizedItems = await PrioritizeActionItemsAsync(actionItems, goal, cancellationToken);
        var sequencedItems = SequenceActionItems(prioritizedItems, request.Timeframe);

        return sequencedItems;
    }

    public async Task<GoalTrackingInsights> GetTrackingInsightsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var userGoals = await _goalRepository.GetByUserIdAsync(userId, includeCompleted: true, cancellationToken);
        var insights = new GoalTrackingInsights
        {
            UserId = userId,
            TimeRange = $"{fromDate:MMM dd, yyyy} - {toDate:MMM dd, yyyy}",
            TotalGoals = userGoals.Count,
            ActiveGoals = userGoals.Count(g => g.Status == GoalStatus.InProgress),
            GoalsAchieved = userGoals.Count(g => g.Status == GoalStatus.Achieved && g.AchievedDate >= fromDate && g.AchievedDate <= toDate),
            AverageProgressRate = CalculateAverageProgressRate(userGoals, fromDate, toDate),
            GoalsByCategory = GroupGoalsByCategory(userGoals),
            TopPerformingGoals = await GetTopPerformingGoalsAsync(userGoals, fromDate, toDate, cancellationToken),
            GoalsNeedingAttention = await GetGoalsNeedingAttentionAsync(userGoals, cancellationToken),
            ProductivityInsights = await GenerateProductivityInsightsAsync(userGoals, fromDate, toDate, cancellationToken),
            Recommendations = await GenerateUserRecommendationsAsync(userId, userGoals, cancellationToken)
        };

        return insights;
    }

    public async Task<List<MilestoneRecommendation>> SuggestMilestonesAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        var goal = await _goalRepository.GetByIdAsync(goalId, cancellationToken);
        if (goal == null)
            throw new ArgumentException($"Goal with ID {goalId} not found");

        var recommendations = new List<MilestoneRecommendation>();

        // Analyze goal type and generate appropriate milestones
        switch (goal.Type)
        {
            case GoalType.Professional:
                recommendations.AddRange(GenerateProfessionalMilestones(goal));
                break;
            case GoalType.Health:
                recommendations.AddRange(GenerateHealthMilestones(goal));
                break;
            case GoalType.Financial:
                recommendations.AddRange(GenerateFinancialMilestones(goal));
                break;
            case GoalType.Educational:
                recommendations.AddRange(GenerateEducationalMilestones(goal));
                break;
            default:
                recommendations.AddRange(GenerateGenericMilestones(goal));
                break;
        }

        // Prioritize and validate milestones
        var validatedMilestones = ValidateMilestones(recommendations, goal);
        return validatedMilestones.OrderBy(m => m.SuggestedOrder).ToList();
    }

    public async Task<GoalOptimizationSuggestions> OptimizeGoalAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        var goal = await _goalRepository.GetByIdAsync(goalId, cancellationToken);
        if (goal == null)
            throw new ArgumentException($"Goal with ID {goalId} not found");

        var relatedTasks = await _todoRepository.GetByGoalIdAsync(goalId, cancellationToken);
        var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goalId, cancellationToken);
        var suggestions = new GoalOptimizationSuggestions
        {
            GoalId = goalId,
            GoalTitle = goal.Title,
            OptimizationScore = CalculateOptimizationScore(goal, relatedTasks, progressHistory),
            Suggestions = new List<OptimizationSuggestion>()
        };

        // Analyze different aspects and generate suggestions
        
        // Timeline optimization
        if (goal.TargetDate.HasValue)
        {
            var timelineAnalysis = AnalyzeTimeline(goal, progressHistory);
            if (timelineAnalysis.IsUnrealistic)
            {
                suggestions.Suggestions.Add(new OptimizationSuggestion
                {
                    Category = OptimizationCategory.Timeline,
                    Title = "Adjust Target Date",
                    Description = $"Based on current progress velocity, consider extending the deadline by {timelineAnalysis.SuggestedExtension} days.",
                    Impact = OptimizationImpact.High,
                    ImplementationEffort = OptimizationEffort.Low,
                    ExpectedBenefit = "Reduces stress and increases likelihood of success"
                });
            }
        }

        // Milestone optimization
        var incompleteMilestones = goal.Milestones.Where(m => !m.IsCompleted).ToList();
        if (incompleteMilestones.Count > 5)
        {
            suggestions.Suggestions.Add(new OptimizationSuggestion
            {
                Category = OptimizationCategory.Structure,
                Title = "Simplify Milestones",
                Description = $"Consider consolidating {incompleteMilestones.Count} remaining milestones into fewer, more manageable chunks.",
                Impact = OptimizationImpact.Medium,
                ImplementationEffort = OptimizationEffort.Medium,
                ExpectedBenefit = "Improved focus and reduced overwhelm"
            });
        }

        // Task alignment optimization
        var unalignedTasks = relatedTasks.Where(t => !IsTaskAlignedWithGoal(t, goal)).ToList();
        if (unalignedTasks.Any())
        {
            suggestions.Suggestions.Add(new OptimizationSuggestion
            {
                Category = OptimizationCategory.Focus,
                Title = "Remove Unaligned Tasks",
                Description = $"Consider removing or reassigning {unalignedTasks.Count} tasks that don't directly contribute to the goal.",
                Impact = OptimizationImpact.Medium,
                ImplementationEffort = OptimizationEffort.Low,
                ExpectedBenefit = "Better focus on activities that directly advance the goal"
            });
        }

        // Progress tracking optimization
        if (progressHistory.Count < 3 && goal.CreatedAt < DateTime.UtcNow.AddWeeks(-4))
        {
            suggestions.Suggestions.Add(new OptimizationSuggestion
            {
                Category = OptimizationCategory.Tracking,
                Title = "Improve Progress Tracking",
                Description = "Set up regular progress reviews and metric tracking to maintain momentum.",
                Impact = OptimizationImpact.High,
                ImplementationEffort = OptimizationEffort.Low,
                ExpectedBenefit = "Better visibility into progress and early detection of issues"
            });
        }

        return suggestions;
    }

    public async Task<List<Goal>> GetGoalsNeedingAttentionAsync(string userId, CancellationToken cancellationToken = default)
    {
        var userGoals = await _goalRepository.GetByUserIdAsync(userId, includeCompleted: false, cancellationToken);
        var goalsNeedingAttention = new List<Goal>();

        foreach (var goal in userGoals)
        {
            var needsAttention = false;
            var reasons = new List<string>();

            // Check for stalled progress
            var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goal.Id, cancellationToken);
            if (IsProgressStalled(progressHistory))
            {
                needsAttention = true;
                reasons.Add("No progress in the last 2 weeks");
            }

            // Check for approaching deadlines
            if (goal.TargetDate.HasValue && goal.TargetDate.Value.Subtract(DateTime.UtcNow).TotalDays <= 30 && goal.Progress < 70)
            {
                needsAttention = true;
                reasons.Add("Deadline approaching with low progress");
            }

            // Check for overdue milestones
            var overdueMilestones = goal.Milestones.Where(m => 
                !m.IsCompleted && 
                m.TargetDate.HasValue && 
                m.TargetDate.Value < DateTime.UtcNow).ToList();
            
            if (overdueMilestones.Any())
            {
                needsAttention = true;
                reasons.Add($"{overdueMilestones.Count} overdue milestones");
            }

            if (needsAttention)
            {
                // Store attention reasons in metadata or tags
                goal.SetMetrics(System.Text.Json.JsonSerializer.Serialize(new { AttentionReasons = reasons }));
                goalsNeedingAttention.Add(goal);
            }
        }

        return goalsNeedingAttention.OrderByDescending(g => CalculateAttentionPriority(g)).ToList();
    }

    public async Task<CourseCorrection> SuggestCourseCorrectionsAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        var goal = await _goalRepository.GetByIdAsync(goalId, cancellationToken);
        if (goal == null)
            throw new ArgumentException($"Goal with ID {goalId} not found");

        var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goalId, cancellationToken);
        var relatedTasks = await _todoRepository.GetByGoalIdAsync(goalId, cancellationToken);

        var correction = new CourseCorrection
        {
            GoalId = goalId,
            GoalTitle = goal.Title,
            CurrentStatus = AnalyzeCurrentStatus(goal, progressHistory),
            IdentifiedIssues = IdentifyIssues(goal, progressHistory, relatedTasks),
            RecommendedActions = new List<CorrectionAction>(),
            PredictedOutcome = await _progressPredictor.PredictOutcomeAsync(goalId, cancellationToken)
        };

        // Generate correction actions based on identified issues
        foreach (var issue in correction.IdentifiedIssues)
        {
            var actions = GenerateCorrectionActionsForIssue(issue, goal);
            correction.RecommendedActions.AddRange(actions);
        }

        // Prioritize actions by impact and effort
        correction.RecommendedActions = correction.RecommendedActions
            .OrderByDescending(a => a.ExpectedImpact)
            .ThenBy(a => a.EffortRequired)
            .ToList();

        return correction;
    }

    // Private helper methods
    private decimal CalculateWeeklyProgress(List<ProgressDataPoint> history)
    {
        var oneWeekAgo = DateTime.UtcNow.AddDays(-7);
        var recentPoints = history.Where(p => p.Date >= oneWeekAgo).OrderBy(p => p.Date).ToList();
        
        if (recentPoints.Count < 2) return 0;
        
        return recentPoints.Last().Value - recentPoints.First().Value;
    }

    private decimal CalculateMonthlyProgress(List<ProgressDataPoint> history)
    {
        var oneMonthAgo = DateTime.UtcNow.AddDays(-30);
        var recentPoints = history.Where(p => p.Date >= oneMonthAgo).OrderBy(p => p.Date).ToList();
        
        if (recentPoints.Count < 2) return 0;
        
        return recentPoints.Last().Value - recentPoints.First().Value;
    }

    private decimal CalculateProgressVelocity(List<ProgressDataPoint> history)
    {
        if (history.Count < 2) return 0;
        
        var orderedHistory = history.OrderBy(p => p.Date).ToList();
        var totalDays = (orderedHistory.Last().Date - orderedHistory.First().Date).TotalDays;
        var totalProgress = orderedHistory.Last().Value - orderedHistory.First().Value;
        
        return totalDays > 0 ? (decimal)(totalProgress / (decimal)totalDays) : 0;
    }

    private string GenerateStatusSummary(Goal goal, List<TodoItem> relatedTasks)
    {
        var completedTasks = relatedTasks.Count(t => t.Status == TodoStatus.Completed);
        var totalTasks = relatedTasks.Count;
        var completedMilestones = goal.Milestones.Count(m => m.IsCompleted);
        var totalMilestones = goal.Milestones.Count;

        return $"Goal is {goal.Progress:F0}% complete with {completedMilestones}/{totalMilestones} milestones achieved and {completedTasks}/{totalTasks} related tasks completed.";
    }

    private async Task<List<string>> GenerateRecommendationsAsync(Goal goal, List<TodoItem> relatedTasks, List<ProgressDataPoint> history, CancellationToken cancellationToken)
    {
        var recommendations = new List<string>();

        // Progress-based recommendations
        if (goal.Progress < 25 && goal.CreatedAt < DateTime.UtcNow.AddMonths(-1))
        {
            recommendations.Add("Consider breaking down the goal into smaller, more achievable milestones");
        }

        if (history.Count > 0 && CalculateProgressVelocity(history) == 0)
        {
            recommendations.Add("Schedule regular time blocks dedicated to working on this goal");
        }

        // Task-based recommendations
        var pendingTasks = relatedTasks.Count(t => t.Status != TodoStatus.Completed);
        if (pendingTasks > 10)
        {
            recommendations.Add("Focus on completing existing tasks before adding new ones");
        }

        return recommendations;
    }

    private List<Milestone> GetNextMilestones(Goal goal)
    {
        return goal.Milestones
            .Where(m => !m.IsCompleted)
            .OrderBy(m => m.TargetDate ?? DateTime.MaxValue)
            .Take(3)
            .ToList();
    }

    private async Task<List<string>> IdentifyBlockersAndRisksAsync(Goal goal, List<TodoItem> relatedTasks, CancellationToken cancellationToken)
    {
        var blockers = new List<string>();

        // Check for overdue milestones
        var overdueMilestones = goal.Milestones.Where(m => 
            !m.IsCompleted && 
            m.TargetDate.HasValue && 
            m.TargetDate.Value < DateTime.UtcNow).ToList();

        if (overdueMilestones.Any())
        {
            blockers.Add($"{overdueMilestones.Count} overdue milestones may be blocking progress");
        }

        // Check for high-priority blocked tasks
        var blockedTasks = relatedTasks.Where(t => 
            t.Status == TodoStatus.NotStarted && 
            t.DependencyIds.Any()).ToList();

        if (blockedTasks.Any())
        {
            blockers.Add($"{blockedTasks.Count} tasks are waiting on dependencies");
        }

        return blockers;
    }

    private async Task<List<ActionItem>> PrioritizeActionItemsAsync(List<ActionItem> actionItems, Goal goal, CancellationToken cancellationToken)
    {
        foreach (var item in actionItems)
        {
            item.Priority = CalculateActionItemPriority(item, goal);
        }

        return actionItems.OrderByDescending(a => a.Priority).ToList();
    }

    private List<ActionItem> SequenceActionItems(List<ActionItem> actionItems, ActionPlanTimeframe timeframe)
    {
        var timeframeDays = timeframe switch
        {
            ActionPlanTimeframe.OneWeek => 7,
            ActionPlanTimeframe.TwoWeeks => 14,
            ActionPlanTimeframe.OneMonth => 30,
            ActionPlanTimeframe.ThreeMonths => 90,
            _ => 30
        };

        var sequencedItems = new List<ActionItem>();
        var currentDate = DateTime.Today;

        foreach (var item in actionItems)
        {
            item.SuggestedStartDate = currentDate;
            item.SuggestedDueDate = currentDate.AddDays(item.EstimatedDays);
            sequencedItems.Add(item);
            
            // Add buffer between tasks
            currentDate = item.SuggestedDueDate.AddDays(1);
            
            if (currentDate > DateTime.Today.AddDays(timeframeDays))
                break;
        }

        return sequencedItems;
    }

    private int CalculateActionItemPriority(ActionItem item, Goal goal)
    {
        int priority = 50; // Base priority

        // Adjust based on impact
        priority += item.Impact switch
        {
            ActionItemImpact.High => 30,
            ActionItemImpact.Medium => 15,
            ActionItemImpact.Low => 0,
            _ => 0
        };

        // Adjust based on effort (prefer low effort for quick wins)
        priority += item.Effort switch
        {
            ActionItemEffort.Low => 15,
            ActionItemEffort.Medium => 5,
            ActionItemEffort.High => -10,
            _ => 0
        };

        // Adjust based on goal urgency
        if (goal.TargetDate.HasValue && goal.TargetDate.Value.Subtract(DateTime.UtcNow).TotalDays < 30)
        {
            priority += 20;
        }

        return Math.Max(0, Math.Min(100, priority));
    }

    private decimal CalculateAverageProgressRate(List<Goal> goals, DateTime fromDate, DateTime toDate)
    {
        var activeGoals = goals.Where(g => g.Status == GoalStatus.InProgress).ToList();
        if (!activeGoals.Any()) return 0;

        return activeGoals.Average(g => g.Progress);
    }

    private Dictionary<GoalType, int> GroupGoalsByCategory(List<Goal> goals)
    {
        return goals.GroupBy(g => g.Type)
                   .ToDictionary(g => g.Key, g => g.Count());
    }

    private async Task<List<GoalPerformanceInfo>> GetTopPerformingGoalsAsync(List<Goal> goals, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken)
    {
        var performanceList = new List<GoalPerformanceInfo>();

        foreach (var goal in goals.Where(g => g.Status == GoalStatus.InProgress))
        {
            var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goal.Id, cancellationToken);
            var progressRate = CalculateProgressVelocity(progressHistory);

            performanceList.Add(new GoalPerformanceInfo
            {
                GoalId = goal.Id,
                GoalTitle = goal.Title,
                CurrentProgress = goal.Progress,
                ProgressRate = progressRate,
                CompletedMilestones = goal.Milestones.Count(m => m.IsCompleted),
                TotalMilestones = goal.Milestones.Count
            });
        }

        return performanceList.OrderByDescending(g => g.ProgressRate)
                             .Take(5)
                             .ToList();
    }

    private async Task<List<ProductivityInsight>> GenerateProductivityInsightsAsync(List<Goal> goals, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken)
    {
        var insights = new List<ProductivityInsight>();

        // Goal completion rate insight
        var completedGoals = goals.Count(g => g.Status == GoalStatus.Achieved && g.AchievedDate >= fromDate && g.AchievedDate <= toDate);
        var totalGoals = goals.Count;

        if (totalGoals > 0)
        {
            var completionRate = (decimal)completedGoals / totalGoals * 100;
            insights.Add(new ProductivityInsight
            {
                Type = InsightType.Achievement,
                Priority = InsightPriority.Medium,
                Title = "Goal Completion Rate",
                Description = $"You've achieved {completionRate:F0}% of your goals this period.",
                Data = new Dictionary<string, object> { { "completionRate", completionRate } }
            });
        }

        return insights;
    }

    private async Task<List<string>> GenerateUserRecommendationsAsync(string userId, List<Goal> goals, CancellationToken cancellationToken)
    {
        var recommendations = new List<string>();

        // Too many active goals
        var activeGoals = goals.Count(g => g.Status == GoalStatus.InProgress);
        if (activeGoals > 5)
        {
            recommendations.Add("Consider focusing on fewer goals simultaneously for better results");
        }

        // Lack of recent progress
        var stagnantGoals = 0;
        foreach (var goal in goals.Where(g => g.Status == GoalStatus.InProgress))
        {
            var progressHistory = await _goalAnalytics.GetProgressHistoryAsync(goal.Id, cancellationToken);
            if (IsProgressStalled(progressHistory))
            {
                stagnantGoals++;
            }
        }

        if (stagnantGoals > 0)
        {
            recommendations.Add($"Review and re-energize {stagnantGoals} goals that haven't seen progress recently");
        }

        return recommendations;
    }

    private bool IsProgressStalled(List<ProgressDataPoint> history)
    {
        var twoWeeksAgo = DateTime.UtcNow.AddDays(-14);
        var recentProgress = history.Where(p => p.Date >= twoWeeksAgo).ToList();
        
        if (recentProgress.Count < 2) return true;
        
        var progressChange = recentProgress.Max(p => p.Value) - recentProgress.Min(p => p.Value);
        return progressChange < 5; // Less than 5% progress in 2 weeks
    }

    private int CalculateAttentionPriority(Goal goal)
    {
        int priority = 0;

        // Higher priority for goals with approaching deadlines
        if (goal.TargetDate.HasValue)
        {
            var daysUntilDeadline = (goal.TargetDate.Value - DateTime.UtcNow).TotalDays;
            if (daysUntilDeadline <= 7) priority += 50;
            else if (daysUntilDeadline <= 30) priority += 30;
        }

        // Higher priority for low progress goals
        if (goal.Progress < 25) priority += 20;
        else if (goal.Progress < 50) priority += 10;

        // Higher priority for high-priority goals
        priority += goal.Priority switch
        {
            GoalPriority.Critical => 40,
            GoalPriority.High => 20,
            GoalPriority.Medium => 10,
            GoalPriority.Low => 0,
            _ => 0
        };

        return priority;
    }

    // Additional helper methods for milestone generation
    private List<MilestoneRecommendation> GenerateProfessionalMilestones(Goal goal)
    {
        return new List<MilestoneRecommendation>
        {
            new() { Title = "Skills Assessment", Description = "Complete assessment of current skills vs. required skills", SuggestedOrder = 1, EstimatedDuration = TimeSpan.FromDays(3) },
            new() { Title = "Learning Plan", Description = "Create detailed learning and development plan", SuggestedOrder = 2, EstimatedDuration = TimeSpan.FromDays(5) },
            new() { Title = "First Implementation", Description = "Apply new skills in a real project or scenario", SuggestedOrder = 3, EstimatedDuration = TimeSpan.FromDays(14) },
            new() { Title = "Feedback Collection", Description = "Gather feedback on progress and performance", SuggestedOrder = 4, EstimatedDuration = TimeSpan.FromDays(7) },
            new() { Title = "Final Demonstration", Description = "Demonstrate mastery of the professional goal", SuggestedOrder = 5, EstimatedDuration = TimeSpan.FromDays(10) }
        };
    }

    private List<MilestoneRecommendation> GenerateHealthMilestones(Goal goal)
    {
        return new List<MilestoneRecommendation>
        {
            new() { Title = "Baseline Assessment", Description = "Establish current health metrics and fitness level", SuggestedOrder = 1, EstimatedDuration = TimeSpan.FromDays(7) },
            new() { Title = "Habit Formation", Description = "Establish consistent daily health habits", SuggestedOrder = 2, EstimatedDuration = TimeSpan.FromDays(21) },
            new() { Title = "First Checkpoint", Description = "Measure progress and adjust plan as needed", SuggestedOrder = 3, EstimatedDuration = TimeSpan.FromDays(30) },
            new() { Title = "Consistency Build", Description = "Maintain habits for extended period", SuggestedOrder = 4, EstimatedDuration = TimeSpan.FromDays(60) },
            new() { Title = "Final Assessment", Description = "Evaluate achievement of health goal", SuggestedOrder = 5, EstimatedDuration = TimeSpan.FromDays(7) }
        };
    }

    private List<MilestoneRecommendation> GenerateFinancialMilestones(Goal goal)
    {
        return new List<MilestoneRecommendation>
        {
            new() { Title = "Financial Analysis", Description = "Complete analysis of current financial situation", SuggestedOrder = 1, EstimatedDuration = TimeSpan.FromDays(5) },
            new() { Title = "Budget Creation", Description = "Develop detailed budget plan", SuggestedOrder = 2, EstimatedDuration = TimeSpan.FromDays(3) },
            new() { Title = "25% Target", Description = "Achieve 25% of financial goal", SuggestedOrder = 3, EstimatedDuration = TimeSpan.FromDays(90) },
            new() { Title = "50% Target", Description = "Reach halfway point of financial goal", SuggestedOrder = 4, EstimatedDuration = TimeSpan.FromDays(90) },
            new() { Title = "Final Target", Description = "Achieve complete financial goal", SuggestedOrder = 5, EstimatedDuration = TimeSpan.FromDays(90) }
        };
    }

    private List<MilestoneRecommendation> GenerateEducationalMilestones(Goal goal)
    {
        return new List<MilestoneRecommendation>
        {
            new() { Title = "Learning Plan", Description = "Create comprehensive learning curriculum", SuggestedOrder = 1, EstimatedDuration = TimeSpan.FromDays(7) },
            new() { Title = "Foundation Building", Description = "Master fundamental concepts", SuggestedOrder = 2, EstimatedDuration = TimeSpan.FromDays(30) },
            new() { Title = "Intermediate Progress", Description = "Complete intermediate level content", SuggestedOrder = 3, EstimatedDuration = TimeSpan.FromDays(45) },
            new() { Title = "Practical Application", Description = "Apply knowledge in real-world projects", SuggestedOrder = 4, EstimatedDuration = TimeSpan.FromDays(30) },
            new() { Title = "Knowledge Validation", Description = "Demonstrate mastery through testing or certification", SuggestedOrder = 5, EstimatedDuration = TimeSpan.FromDays(14) }
        };
    }

    private List<MilestoneRecommendation> GenerateGenericMilestones(Goal goal)
    {
        return new List<MilestoneRecommendation>
        {
            new() { Title = "Planning Phase", Description = "Complete detailed planning for goal achievement", SuggestedOrder = 1, EstimatedDuration = TimeSpan.FromDays(7) },
            new() { Title = "Early Progress", Description = "Achieve first 20% of goal", SuggestedOrder = 2, EstimatedDuration = TimeSpan.FromDays(30) },
            new() { Title = "Midpoint Check", Description = "Reach 50% completion and review progress", SuggestedOrder = 3, EstimatedDuration = TimeSpan.FromDays(60) },
            new() { Title = "Final Push", Description = "Complete remaining 30% of goal", SuggestedOrder = 4, EstimatedDuration = TimeSpan.FromDays(45) },
            new() { Title = "Goal Achievement", Description = "Complete goal and document lessons learned", SuggestedOrder = 5, EstimatedDuration = TimeSpan.FromDays(7) }
        };
    }

    private List<MilestoneRecommendation> ValidateMilestones(List<MilestoneRecommendation> milestones, Goal goal)
    {
        // Remove milestones that conflict with existing ones
        var existingTitles = goal.Milestones.Select(m => m.Title.ToLower()).ToHashSet();
        return milestones.Where(m => !existingTitles.Contains(m.Title.ToLower())).ToList();
    }

    private decimal CalculateOptimizationScore(Goal goal, List<TodoItem> tasks, List<ProgressDataPoint> history)
    {
        decimal score = 100;

        // Reduce score for stalled progress
        if (IsProgressStalled(history)) score -= 30;

        // Reduce score for overdue milestones
        var overdueMilestones = goal.Milestones.Count(m => !m.IsCompleted && m.TargetDate < DateTime.UtcNow);
        score -= overdueMilestones * 10;

        // Reduce score for too many incomplete tasks
        var incompleteTasks = tasks.Count(t => t.Status != TodoStatus.Completed);
        if (incompleteTasks > 10) score -= 20;

        return Math.Max(0, score);
    }

    private TimelineAnalysis AnalyzeTimeline(Goal goal, List<ProgressDataPoint> history)
    {
        if (!goal.TargetDate.HasValue)
            return new TimelineAnalysis { IsRealistic = true };

        var velocity = CalculateProgressVelocity(history);
        var remainingProgress = 100 - goal.Progress;
        var remainingDays = (goal.TargetDate.Value - DateTime.UtcNow).TotalDays;

        var requiredVelocity = remainingDays > 0 ? remainingProgress / (decimal)remainingDays : decimal.MaxValue;

        return new TimelineAnalysis
        {
            IsRealistic = velocity >= requiredVelocity * 0.8m, // 20% buffer
            IsUnrealistic = velocity < requiredVelocity * 0.5m,
            SuggestedExtension = velocity > 0 ? (int)Math.Ceiling((double)(remainingProgress / velocity)) : 90
        };
    }

    private bool IsTaskAlignedWithGoal(TodoItem task, Goal goal)
    {
        // Simple alignment check - in practice, this could be more sophisticated
        return task.GoalId == goal.Id;
    }

    private GoalStatusAnalysis AnalyzeCurrentStatus(Goal goal, List<ProgressDataPoint> history)
    {
        return new GoalStatusAnalysis
        {
            ProgressRate = CalculateProgressVelocity(history),
            IsOnTrack = !IsProgressStalled(history) && goal.Progress > 0,
            DaysUntilDeadline = goal.TargetDate.HasValue ? (int)(goal.TargetDate.Value - DateTime.UtcNow).TotalDays : null,
            CompletionProbability = CalculateCompletionProbability(goal, history)
        };
    }

    private List<GoalIssue> IdentifyIssues(Goal goal, List<ProgressDataPoint> history, List<TodoItem> tasks)
    {
        var issues = new List<GoalIssue>();

        if (IsProgressStalled(history))
        {
            issues.Add(new GoalIssue
            {
                Type = GoalIssueType.StalledProgress,
                Severity = IssueSeverity.High,
                Description = "No significant progress in the last 2 weeks"
            });
        }

        var overdueMilestones = goal.Milestones.Count(m => !m.IsCompleted && m.TargetDate < DateTime.UtcNow);
        if (overdueMilestones > 0)
        {
            issues.Add(new GoalIssue
            {
                Type = GoalIssueType.OverdueMilestones,
                Severity = IssueSeverity.Medium,
                Description = $"{overdueMilestones} milestones are overdue"
            });
        }

        return issues;
    }

    private List<CorrectionAction> GenerateCorrectionActionsForIssue(GoalIssue issue, Goal goal)
    {
        return issue.Type switch
        {
            GoalIssueType.StalledProgress => new List<CorrectionAction>
            {
                new() { Description = "Schedule daily focused work sessions", ExpectedImpact = 8, EffortRequired = 3 },
                new() { Description = "Break down next milestone into smaller tasks", ExpectedImpact = 7, EffortRequired = 2 }
            },
            GoalIssueType.OverdueMilestones => new List<CorrectionAction>
            {
                new() { Description = "Reassess milestone deadlines and adjust", ExpectedImpact = 6, EffortRequired = 2 },
                new() { Description = "Focus on completing overdue milestones before new work", ExpectedImpact = 8, EffortRequired = 4 }
            },
            _ => new List<CorrectionAction>()
        };
    }

    private decimal CalculateCompletionProbability(Goal goal, List<ProgressDataPoint> history)
    {
        if (!goal.TargetDate.HasValue) return 75; // Default probability

        var velocity = CalculateProgressVelocity(history);
        var remainingProgress = 100 - goal.Progress;
        var remainingDays = (goal.TargetDate.Value - DateTime.UtcNow).TotalDays;

        if (remainingDays <= 0) return goal.Progress >= 100 ? 100 : 0;
        if (velocity <= 0) return 10; // Very low probability if no progress

        var projectedProgress = goal.Progress + (velocity * (decimal)remainingDays);
        return Math.Min(100, Math.Max(0, projectedProgress));
    }

    // Supporting classes for internal use
    private class TimelineAnalysis
    {
        public bool IsRealistic { get; set; }
        public bool IsUnrealistic { get; set; }
        public int SuggestedExtension { get; set; }
    }
}