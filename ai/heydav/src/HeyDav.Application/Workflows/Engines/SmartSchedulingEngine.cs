using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Engines;

public class SmartSchedulingEngine : ISmartSchedulingEngine
{
    private readonly IProductivityPatternAnalyzer _patternAnalyzer;
    private readonly IEnergyLevelPredictor _energyPredictor;
    private readonly ICalendarIntegration _calendarIntegration;
    private readonly ISchedulingOptimizer _optimizer;

    public SmartSchedulingEngine(
        IProductivityPatternAnalyzer patternAnalyzer,
        IEnergyLevelPredictor energyPredictor,
        ICalendarIntegration calendarIntegration,
        ISchedulingOptimizer optimizer)
    {
        _patternAnalyzer = patternAnalyzer;
        _energyPredictor = energyPredictor;
        _calendarIntegration = calendarIntegration;
        _optimizer = optimizer;
    }

    public async Task<ScheduleOptimizationResult> OptimizeScheduleAsync(
        OptimizeScheduleRequest request,
        CancellationToken cancellationToken = default)
    {
        // Analyze user's productivity patterns
        var patterns = await _patternAnalyzer.AnalyzeUserPatternsAsync(request.UserId, cancellationToken);
        
        // Predict energy levels for the scheduling period
        var energyPredictions = await _energyPredictor.PredictEnergyLevelsAsync(
            request.UserId, 
            request.StartDate, 
            request.EndDate, 
            cancellationToken);

        // Get existing calendar commitments
        var existingCommitments = await _calendarIntegration.GetCommitmentsAsync(
            request.UserId, 
            request.StartDate, 
            request.EndDate, 
            cancellationToken);

        // Create optimization context
        var context = new SchedulingContext
        {
            UserId = request.UserId,
            StartDate = request.StartDate,
            EndDate = request.EndDate,
            Tasks = request.Tasks,
            Goals = request.Goals,
            Preferences = request.Preferences,
            Patterns = patterns,
            EnergyPredictions = energyPredictions,
            ExistingCommitments = existingCommitments
        };

        // Run optimization algorithm
        var optimizedSchedule = await _optimizer.OptimizeAsync(context, cancellationToken);

        // Generate insights and recommendations
        var insights = GenerateSchedulingInsights(context, optimizedSchedule);

        return new ScheduleOptimizationResult
        {
            OptimizedSchedule = optimizedSchedule,
            Insights = insights,
            ConfidenceScore = CalculateConfidenceScore(context, optimizedSchedule),
            AlternativeOptions = await GenerateAlternativesAsync(context, optimizedSchedule, cancellationToken)
        };
    }

    public async Task<TimeSlotRecommendation> RecommendTimeSlotAsync(
        TimeSlotRecommendationRequest request,
        CancellationToken cancellationToken = default)
    {
        var patterns = await _patternAnalyzer.AnalyzeUserPatternsAsync(request.UserId, cancellationToken);
        var energyPredictions = await _energyPredictor.PredictEnergyLevelsAsync(
            request.UserId, 
            request.PreferredDate ?? DateTime.Today.AddDays(1), 
            request.PreferredDate?.AddDays(7) ?? DateTime.Today.AddDays(8), 
            cancellationToken);

        var availableSlots = await FindAvailableTimeSlotsAsync(
            request.UserId,
            request.Duration,
            request.PreferredDate,
            request.EarliestTime,
            request.LatestTime,
            cancellationToken);

        var rankedSlots = RankTimeSlots(availableSlots, patterns, energyPredictions, request);

        return new TimeSlotRecommendation
        {
            RecommendedSlot = rankedSlots.FirstOrDefault(),
            AlternativeSlots = rankedSlots.Skip(1).Take(5).ToList(),
            Reasoning = GenerateSlotRecommendationReasoning(rankedSlots.FirstOrDefault(), patterns, energyPredictions)
        };
    }

    public async Task<List<ProductivityInsight>> AnalyzeProductivityPatternsAsync(
        string userId,
        DateTime fromDate,
        DateTime toDate,
        CancellationToken cancellationToken = default)
    {
        var patterns = await _patternAnalyzer.AnalyzeUserPatternsAsync(userId, fromDate, toDate, cancellationToken);
        var insights = new List<ProductivityInsight>();

        // Peak productivity hours
        if (patterns.PeakHours.Any())
        {
            insights.Add(new ProductivityInsight
            {
                Type = InsightType.Pattern,
                Priority = InsightPriority.High,
                Title = "Peak Productivity Hours Identified",
                Description = $"You're most productive between {FormatTimeRange(patterns.PeakHours)}. Schedule your most important tasks during these hours.",
                Data = new Dictionary<string, object> { { "peakHours", patterns.PeakHours } },
                IsActionable = true,
                RecommendedAction = "Block these hours for deep work and important tasks"
            });
        }

        // Energy patterns
        if (patterns.EnergyPatterns.Any())
        {
            var lowEnergyPeriods = patterns.EnergyPatterns.Where(p => p.Value < 4).ToList();
            if (lowEnergyPeriods.Any())
            {
                insights.Add(new ProductivityInsight
                {
                    Type = InsightType.Warning,
                    Priority = InsightPriority.Medium,
                    Title = "Low Energy Periods Detected",
                    Description = $"You typically have low energy during {FormatLowEnergyPeriods(lowEnergyPeriods)}. Consider scheduling lighter tasks during these times.",
                    Data = new Dictionary<string, object> { { "lowEnergyPeriods", lowEnergyPeriods } },
                    IsActionable = true,
                    RecommendedAction = "Schedule administrative tasks, email, or breaks during low energy periods"
                });
            }
        }

        // Task completion patterns
        if (patterns.TaskCompletionPatterns.Any())
        {
            var bestDays = patterns.TaskCompletionPatterns
                .OrderByDescending(p => p.CompletionRate)
                .Take(3)
                .ToList();

            insights.Add(new ProductivityInsight
            {
                Type = InsightType.Achievement,
                Priority = InsightPriority.Medium,
                Title = "Most Productive Days",
                Description = $"You complete the most tasks on {string.Join(", ", bestDays.Select(d => d.DayOfWeek))}. Consider scheduling important deadlines on these days.",
                Data = new Dictionary<string, object> { { "bestDays", bestDays } },
                IsActionable = true,
                RecommendedAction = "Schedule important deadlines and challenging tasks on your most productive days"
            });
        }

        // Context switching analysis
        if (patterns.ContextSwitchingFrequency > 10) // More than 10 switches per day
        {
            insights.Add(new ProductivityInsight
            {
                Type = InsightType.Opportunity,
                Priority = InsightPriority.High,
                Title = "High Context Switching Detected",
                Description = $"You switch between tasks {patterns.ContextSwitchingFrequency} times per day on average. This may be impacting your productivity.",
                Data = new Dictionary<string, object> { { "switchingFrequency", patterns.ContextSwitchingFrequency } },
                IsActionable = true,
                RecommendedAction = "Try time blocking similar tasks together and use the Pomodoro technique"
            });
        }

        // Meeting impact analysis
        if (patterns.MeetingImpactScore < 3) // Low meeting effectiveness
        {
            insights.Add(new ProductivityInsight
            {
                Type = InsightType.Warning,
                Priority = InsightPriority.Medium,
                Title = "Meeting Effectiveness Could Improve",
                Description = "Your productivity tends to decrease on days with many meetings. Consider optimizing your meeting schedule.",
                Data = new Dictionary<string, object> { { "meetingImpactScore", patterns.MeetingImpactScore } },
                IsActionable = true,
                RecommendedAction = "Group meetings together, ensure clear agendas, and protect deep work time"
            });
        }

        return insights;
    }

    public async Task<ConflictResolution> ResolveSchedulingConflictAsync(
        SchedulingConflictRequest request,
        CancellationToken cancellationToken = default)
    {
        var patterns = await _patternAnalyzer.AnalyzeUserPatternsAsync(request.UserId, cancellationToken);
        
        var resolutionOptions = new List<ConflictResolutionOption>();

        // Option 1: Reschedule lower priority item
        var lowerPriorityItem = request.ConflictingItems.OrderBy(i => i.Priority).First();
        var alternativeSlots = await FindAvailableTimeSlotsAsync(
            request.UserId,
            lowerPriorityItem.Duration,
            lowerPriorityItem.PreferredDate,
            cancellationToken: cancellationToken);

        if (alternativeSlots.Any())
        {
            resolutionOptions.Add(new ConflictResolutionOption
            {
                Type = ConflictResolutionType.Reschedule,
                Description = $"Reschedule '{lowerPriorityItem.Title}' to {alternativeSlots.First().StartTime:HH:mm} on {alternativeSlots.First().Date:MMM dd}",
                Impact = CalculateRescheduleImpact(lowerPriorityItem, alternativeSlots.First(), patterns),
                Confidence = 0.8m
            });
        }

        // Option 2: Split longer tasks
        var longerTasks = request.ConflictingItems.Where(i => i.Duration > TimeSpan.FromHours(1)).ToList();
        foreach (var task in longerTasks)
        {
            resolutionOptions.Add(new ConflictResolutionOption
            {
                Type = ConflictResolutionType.Split,
                Description = $"Split '{task.Title}' into smaller time blocks across multiple days",
                Impact = CalculateSplitImpact(task, patterns),
                Confidence = 0.6m
            });
        }

        // Option 3: Adjust time boundaries
        if (request.ConflictingItems.Any(i => i.IsFlexible))
        {
            resolutionOptions.Add(new ConflictResolutionOption
            {
                Type = ConflictResolutionType.Adjust,
                Description = "Adjust flexible items to earlier or later time slots",
                Impact = 0.2m, // Low impact
                Confidence = 0.9m
            });
        }

        return new ConflictResolution
        {
            ConflictId = request.ConflictId,
            Options = resolutionOptions.OrderByDescending(o => o.Confidence).ToList(),
            RecommendedOption = resolutionOptions.OrderByDescending(o => o.Confidence).FirstOrDefault(),
            AutoResolutionPossible = resolutionOptions.Any(o => o.Confidence > 0.8m)
        };
    }

    public async Task<FocusTimeRecommendation> RecommendFocusTimeAsync(
        FocusTimeRequest request,
        CancellationToken cancellationToken = default)
    {
        var patterns = await _patternAnalyzer.AnalyzeUserPatternsAsync(request.UserId, cancellationToken);
        var energyPredictions = await _energyPredictor.PredictEnergyLevelsAsync(
            request.UserId,
            request.Date,
            request.Date.AddDays(1),
            cancellationToken);

        // Find optimal focus time slots based on energy levels and historical patterns
        var optimalSlots = new List<FocusTimeSlot>();

        // Morning focus time (if user is a morning person)
        if (patterns.PeakHours.Any(h => h.Hours < 12))
        {
            var morningSlot = new FocusTimeSlot
            {
                StartTime = new TimeSpan(8, 0, 0),
                Duration = TimeSpan.FromHours(2),
                EnergyLevel = energyPredictions.GetValueOrDefault(new TimeSpan(9, 0, 0), 7),
                DistractionLevel = 2, // Low distractions in morning
                RecommendedTaskTypes = new[] { "Deep work", "Complex problem solving", "Creative tasks" }
            };
            optimalSlots.Add(morningSlot);
        }

        // Afternoon focus time
        if (patterns.PeakHours.Any(h => h.Hours >= 13 && h.Hours <= 16))
        {
            var afternoonSlot = new FocusTimeSlot
            {
                StartTime = new TimeSpan(14, 0, 0),
                Duration = TimeSpan.FromMinutes(90),
                EnergyLevel = energyPredictions.GetValueOrDefault(new TimeSpan(14, 30, 0), 6),
                DistractionLevel = 4, // Moderate distractions
                RecommendedTaskTypes = new[] { "Analysis", "Planning", "Review work" }
            };
            optimalSlots.Add(afternoonSlot);
        }

        // Early evening focus time (if patterns show productivity)
        if (patterns.PeakHours.Any(h => h.Hours >= 17 && h.Hours <= 19))
        {
            var eveningSlot = new FocusTimeSlot
            {
                StartTime = new TimeSpan(17, 30, 0),
                Duration = TimeSpan.FromMinutes(60),
                EnergyLevel = energyPredictions.GetValueOrDefault(new TimeSpan(18, 0, 0), 5),
                DistractionLevel = 3, // Lower distractions as day winds down
                RecommendedTaskTypes = new[] { "Planning", "Reflection", "Learning" }
            };
            optimalSlots.Add(eveningSlot);
        }

        return new FocusTimeRecommendation
        {
            Date = request.Date,
            RecommendedSlots = optimalSlots.OrderByDescending(s => s.EnergyLevel).ToList(),
            TotalFocusTime = optimalSlots.Sum(s => s.Duration.TotalMinutes),
            OptimalBreakIntervals = CalculateOptimalBreaks(optimalSlots, patterns),
            EnvironmentRecommendations = GenerateEnvironmentRecommendations(patterns)
        };
    }

    private async Task<List<AvailableTimeSlot>> FindAvailableTimeSlotsAsync(
        string userId,
        TimeSpan duration,
        DateTime? preferredDate = null,
        TimeSpan? earliestTime = null,
        TimeSpan? latestTime = null,
        CancellationToken cancellationToken = default)
    {
        var searchDate = preferredDate ?? DateTime.Today.AddDays(1);
        var startTime = earliestTime ?? new TimeSpan(8, 0, 0);
        var endTime = latestTime ?? new TimeSpan(18, 0, 0);

        var existingCommitments = await _calendarIntegration.GetCommitmentsAsync(
            userId, searchDate, searchDate.AddDays(1), cancellationToken);

        var availableSlots = new List<AvailableTimeSlot>();
        var currentTime = startTime;

        while (currentTime.Add(duration) <= endTime)
        {
            var proposedSlot = new AvailableTimeSlot
            {
                Date = searchDate,
                StartTime = currentTime,
                Duration = duration
            };

            // Check if slot conflicts with existing commitments
            var hasConflict = existingCommitments.Any(c => 
                c.StartTime < proposedSlot.StartTime.Add(duration) && 
                c.EndTime > proposedSlot.StartTime);

            if (!hasConflict)
            {
                availableSlots.Add(proposedSlot);
            }

            currentTime = currentTime.Add(TimeSpan.FromMinutes(30)); // 30-minute increments
        }

        return availableSlots;
    }

    private List<AvailableTimeSlot> RankTimeSlots(
        List<AvailableTimeSlot> slots,
        UserProductivityPatterns patterns,
        Dictionary<TimeSpan, int> energyPredictions,
        TimeSlotRecommendationRequest request)
    {
        return slots.Select(slot => new
        {
            Slot = slot,
            Score = CalculateTimeSlotScore(slot, patterns, energyPredictions, request)
        })
        .OrderByDescending(x => x.Score)
        .Select(x => x.Slot)
        .ToList();
    }

    private decimal CalculateTimeSlotScore(
        AvailableTimeSlot slot,
        UserProductivityPatterns patterns,
        Dictionary<TimeSpan, int> energyPredictions,
        TimeSlotRecommendationRequest request)
    {
        decimal score = 0;

        // Energy level score (0-40 points)
        var energyLevel = energyPredictions.GetValueOrDefault(slot.StartTime, 5);
        score += energyLevel * 4;

        // Peak hours alignment (0-30 points)
        if (patterns.PeakHours.Any(h => Math.Abs((h - slot.StartTime).TotalHours) < 1))
        {
            score += 30;
        }

        // Task type alignment (0-20 points)
        if (request.TaskType == WorkflowStepType.Action && energyLevel >= 7)
        {
            score += 20; // High energy needed for action tasks
        }
        else if (request.TaskType == WorkflowStepType.Review && energyLevel >= 5)
        {
            score += 15; // Moderate energy for review tasks
        }

        // Preferred time bonus (0-10 points)
        if (request.PreferredTime.HasValue)
        {
            var timeDifference = Math.Abs((request.PreferredTime.Value - slot.StartTime).TotalHours);
            score += Math.Max(0, 10 - (decimal)timeDifference);
        }

        return Math.Min(score, 100);
    }

    private List<ProductivityInsight> GenerateSchedulingInsights(
        SchedulingContext context,
        OptimizedSchedule schedule)
    {
        var insights = new List<ProductivityInsight>();

        // Energy alignment insight
        var energyAlignedTasks = schedule.ScheduledTasks.Count(t => 
            context.EnergyPredictions.GetValueOrDefault(t.StartTime.TimeOfDay, 5) >= 7);
        
        var energyAlignmentPercentage = (decimal)energyAlignedTasks / schedule.ScheduledTasks.Count * 100;

        insights.Add(new ProductivityInsight
        {
            Type = InsightType.Achievement,
            Priority = InsightPriority.Medium,
            Title = "Energy Alignment Optimization",
            Description = $"{energyAlignmentPercentage:F0}% of your tasks are scheduled during high-energy periods.",
            Data = new Dictionary<string, object> 
            { 
                { "alignmentPercentage", energyAlignmentPercentage },
                { "energyAlignedTasks", energyAlignedTasks }
            }
        });

        return insights;
    }

    private decimal CalculateConfidenceScore(SchedulingContext context, OptimizedSchedule schedule)
    {
        decimal score = 100;

        // Reduce confidence based on conflicts
        score -= schedule.Conflicts.Count * 10;

        // Reduce confidence based on energy misalignment
        var lowEnergyTasks = schedule.ScheduledTasks.Count(t => 
            context.EnergyPredictions.GetValueOrDefault(t.StartTime.TimeOfDay, 5) < 4);
        score -= lowEnergyTasks * 5;

        // Reduce confidence based on preference violations
        var preferenceViolations = CalculatePreferenceViolations(context.Preferences, schedule);
        score -= preferenceViolations * 3;

        return Math.Max(score, 0);
    }

    private async Task<List<OptimizedSchedule>> GenerateAlternativesAsync(
        SchedulingContext context,
        OptimizedSchedule primarySchedule,
        CancellationToken cancellationToken)
    {
        var alternatives = new List<OptimizedSchedule>();

        // Generate alternative with different strategy
        var alternativeContext = context with 
        { 
            Preferences = context.Preferences with 
            { 
                Strategy = context.Preferences.Strategy == SchedulingStrategy.EnergyBased 
                    ? SchedulingStrategy.PriorityBased 
                    : SchedulingStrategy.EnergyBased 
            } 
        };

        var alternativeSchedule = await _optimizer.OptimizeAsync(alternativeContext, cancellationToken);
        alternatives.Add(alternativeSchedule);

        return alternatives;
    }

    private string FormatTimeRange(List<TimeSpan> hours)
    {
        if (!hours.Any()) return "unknown times";
        
        var earliest = hours.Min();
        var latest = hours.Max();
        return $"{earliest:hh\\:mm} and {latest:hh\\:mm}";
    }

    private string FormatLowEnergyPeriods(List<KeyValuePair<TimeSpan, int>> periods)
    {
        return string.Join(", ", periods.Select(p => p.Key.ToString(@"hh\:mm")));
    }

    private decimal CalculateRescheduleImpact(SchedulingItem item, AvailableTimeSlot newSlot, UserProductivityPatterns patterns)
    {
        // Calculate impact based on energy level changes, timing preferences, etc.
        return 0.3m; // Placeholder
    }

    private decimal CalculateSplitImpact(SchedulingItem item, UserProductivityPatterns patterns)
    {
        // Calculate impact of splitting a task
        return 0.4m; // Placeholder - generally moderate impact
    }

    private int CalculatePreferenceViolations(SchedulingPreferences preferences, OptimizedSchedule schedule)
    {
        int violations = 0;

        // Check preferred time boundaries
        if (preferences.PreferredStartTime.HasValue)
        {
            violations += schedule.ScheduledTasks.Count(t => t.StartTime.TimeOfDay < preferences.PreferredStartTime.Value);
        }

        if (preferences.PreferredEndTime.HasValue)
        {
            violations += schedule.ScheduledTasks.Count(t => t.StartTime.TimeOfDay.Add(t.Duration) > preferences.PreferredEndTime.Value);
        }

        // Check day preferences
        violations += schedule.ScheduledTasks.Count(t => preferences.AvoidDays.Contains(t.StartTime.DayOfWeek));

        return violations;
    }

    private List<TimeSpan> CalculateOptimalBreaks(List<FocusTimeSlot> focusSlots, UserProductivityPatterns patterns)
    {
        var breaks = new List<TimeSpan>();
        
        // Add breaks between focus sessions
        for (int i = 0; i < focusSlots.Count - 1; i++)
        {
            var currentEnd = focusSlots[i].StartTime.Add(focusSlots[i].Duration);
            var nextStart = focusSlots[i + 1].StartTime;
            
            if (nextStart.Subtract(currentEnd).TotalMinutes >= 15)
            {
                breaks.Add(currentEnd);
            }
        }

        return breaks;
    }

    private List<string> GenerateEnvironmentRecommendations(UserProductivityPatterns patterns)
    {
        var recommendations = new List<string>
        {
            "Find a quiet space with minimal distractions",
            "Ensure good lighting and comfortable temperature",
            "Have water and healthy snacks available",
            "Turn off non-essential notifications"
        };

        // Add personalized recommendations based on patterns
        if (patterns.ContextSwitchingFrequency > 8)
        {
            recommendations.Add("Use website blockers to prevent distracting site access");
            recommendations.Add("Put your phone in airplane mode or another room");
        }

        return recommendations;
    }
}