using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;
using HeyDav.Domain.Workflows.Enums;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Productivity;

public class SchedulingOptimizer : ISchedulingOptimizer
{
    private readonly ILogger<SchedulingOptimizer> _logger;

    public SchedulingOptimizer(ILogger<SchedulingOptimizer> logger)
    {
        _logger = logger;
    }

    public async Task<OptimizedSchedule> OptimizeAsync(SchedulingContext context, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Optimizing schedule for user {UserId} with {TaskCount} tasks", 
            context.UserId, context.Tasks.Count);

        var schedule = new OptimizedSchedule();
        var availableSlots = GenerateAvailableTimeSlots(context);
        var sortedTasks = SortTasksByPriority(context.Tasks, context.Preferences.Strategy);

        foreach (var task in sortedTasks)
        {
            var bestSlot = FindBestTimeSlot(task, availableSlots, context);
            if (bestSlot != null)
            {
                var scheduledTask = new ScheduledTask
                {
                    TaskId = task.Id,
                    Title = task.Title,
                    StartTime = bestSlot.Date.Add(bestSlot.StartTime),
                    Duration = task.Duration,
                    Priority = task.Priority,
                    PredictedEnergyLevel = context.EnergyPredictions.GetValueOrDefault(bestSlot.StartTime, 5),
                    SchedulingScore = bestSlot.Score
                };

                schedule.ScheduledTasks.Add(scheduledTask);
                
                // Remove the used time slot
                availableSlots.Remove(bestSlot);
                
                // Add breaks if needed
                if (task.Duration > TimeSpan.FromHours(1))
                {
                    AddScheduledBreak(schedule, scheduledTask, context.Preferences);
                }
            }
            else
            {
                // Task couldn't be scheduled - add to conflicts
                schedule.Conflicts.Add(new SchedulingConflict
                {
                    Id = Guid.NewGuid(),
                    Description = $"Could not find suitable time slot for task '{task.Title}'",
                    ConflictingTaskIds = new List<Guid> { task.Id },
                    Severity = ConflictSeverity.Medium,
                    ConflictTime = context.StartDate,
                    PossibleResolutions = new List<string>
                    {
                        "Extend scheduling timeframe",
                        "Reduce task duration",
                        "Lower priority requirements"
                    }
                });
            }
        }

        schedule.OptimizationScore = await CalculateScheduleScoreAsync(schedule, context, cancellationToken);
        schedule.TotalScheduledTime = TimeSpan.FromMinutes(schedule.ScheduledTasks.Sum(t => t.Duration.TotalMinutes));
        schedule.TotalFreeTime = CalculateFreeTime(context, schedule);

        _logger.LogInformation("Schedule optimization completed. Score: {Score}, Scheduled: {ScheduledTasks}/{TotalTasks}", 
            schedule.OptimizationScore, schedule.ScheduledTasks.Count, context.Tasks.Count);

        return schedule;
    }

    public async Task<List<SchedulingConflict>> DetectConflictsAsync(List<ScheduledTask> tasks, CancellationToken cancellationToken = default)
    {
        var conflicts = new List<SchedulingConflict>();

        for (int i = 0; i < tasks.Count; i++)
        {
            for (int j = i + 1; j < tasks.Count; j++)
            {
                var task1 = tasks[i];
                var task2 = tasks[j];

                if (TasksOverlap(task1, task2))
                {
                    conflicts.Add(new SchedulingConflict
                    {
                        Id = Guid.NewGuid(),
                        Description = $"Time overlap between '{task1.Title}' and '{task2.Title}'",
                        ConflictingTaskIds = new List<Guid> { task1.TaskId, task2.TaskId },
                        Severity = ConflictSeverity.High,
                        ConflictTime = task1.StartTime,
                        PossibleResolutions = new List<string>
                        {
                            "Reschedule one task",
                            "Reduce duration of one task",
                            "Split tasks across different days"
                        }
                    });
                }
            }
        }

        return conflicts;
    }

    public async Task<decimal> CalculateScheduleScoreAsync(OptimizedSchedule schedule, SchedulingContext context, CancellationToken cancellationToken = default)
    {
        decimal score = 0;
        var maxScore = schedule.ScheduledTasks.Count * 100;

        if (maxScore == 0) return 0;

        foreach (var task in schedule.ScheduledTasks)
        {
            var taskScore = 0m;

            // Energy alignment (0-30 points)
            var energyLevel = context.EnergyPredictions.GetValueOrDefault(task.StartTime.TimeOfDay, 5);
            taskScore += Math.Min(30, energyLevel * 3);

            // Priority alignment (0-25 points)
            taskScore += task.Priority * 2.5m;

            // Peak hours alignment (0-20 points)
            if (context.Patterns.PeakHours.Any(h => Math.Abs((h - task.StartTime.TimeOfDay).TotalHours) < 1))
            {
                taskScore += 20;
            }

            // Preference alignment (0-15 points)
            if (IsWithinPreferredHours(task, context.Preferences))
            {
                taskScore += 15;
            }

            // Duration appropriateness (0-10 points)
            if (task.Duration <= TimeSpan.FromHours(2))
            {
                taskScore += 10;
            }
            else if (task.Duration <= TimeSpan.FromHours(4))
            {
                taskScore += 5;
            }

            score += taskScore;
        }

        // Penalty for conflicts
        score -= schedule.Conflicts.Count * 20;

        // Normalize to 0-100 scale
        return Math.Max(0, Math.Min(100, (score / maxScore) * 100));
    }

    private List<AvailableTimeSlot> GenerateAvailableTimeSlots(SchedulingContext context)
    {
        var slots = new List<AvailableTimeSlot>();
        var currentDate = context.StartDate;

        while (currentDate <= context.EndDate)
        {
            var startTime = context.Preferences.PreferredStartTime ?? new TimeSpan(8, 0, 0);
            var endTime = context.Preferences.PreferredEndTime ?? new TimeSpan(18, 0, 0);

            // Skip weekends if not allowed
            if (!context.Preferences.AllowWeekends && 
                (currentDate.DayOfWeek == DayOfWeek.Saturday || currentDate.DayOfWeek == DayOfWeek.Sunday))
            {
                currentDate = currentDate.AddDays(1);
                continue;
            }

            // Skip avoided days
            if (context.Preferences.AvoidDays.Contains(currentDate.DayOfWeek))
            {
                currentDate = currentDate.AddDays(1);
                continue;
            }

            // Generate time slots for the day
            var currentTime = startTime;
            while (currentTime.Add(TimeSpan.FromMinutes(30)) <= endTime)
            {
                // Check if slot conflicts with existing commitments
                var slotEnd = currentTime.Add(TimeSpan.FromMinutes(30));
                var hasConflict = context.ExistingCommitments.Any(c =>
                    c.StartTime.Date == currentDate.Date &&
                    c.StartTime.TimeOfDay < slotEnd &&
                    c.EndTime.TimeOfDay > currentTime);

                if (!hasConflict)
                {
                    var energyLevel = context.EnergyPredictions.GetValueOrDefault(currentTime, 5);
                    slots.Add(new AvailableTimeSlot
                    {
                        Date = currentDate,
                        StartTime = currentTime,
                        Duration = TimeSpan.FromMinutes(30),
                        PredictedEnergyLevel = energyLevel,
                        Score = CalculateSlotScore(currentTime, energyLevel, context)
                    });
                }

                currentTime = currentTime.Add(TimeSpan.FromMinutes(30));
            }

            currentDate = currentDate.AddDays(1);
        }

        return slots.OrderByDescending(s => s.Score).ToList();
    }

    private List<SchedulingItem> SortTasksByPriority(List<SchedulingItem> tasks, SchedulingStrategy strategy)
    {
        return strategy switch
        {
            SchedulingStrategy.PriorityBased => tasks.OrderByDescending(t => t.Priority).ToList(),
            SchedulingStrategy.DeadlineBased => tasks.OrderBy(t => t.PreferredDate ?? DateTime.MaxValue).ToList(),
            SchedulingStrategy.EnergyBased => tasks.OrderByDescending(t => t.RequiredEnergyLevel).ToList(),
            SchedulingStrategy.Balanced => tasks.OrderByDescending(t => t.Priority * 0.4m + t.RequiredEnergyLevel * 0.3m + (t.PreferredDate.HasValue ? 0.3m : 0)).ToList(),
            _ => tasks.OrderByDescending(t => t.Priority).ToList()
        };
    }

    private AvailableTimeSlot? FindBestTimeSlot(SchedulingItem task, List<AvailableTimeSlot> availableSlots, SchedulingContext context)
    {
        var requiredSlots = (int)Math.Ceiling(task.Duration.TotalMinutes / 30.0);
        
        for (int i = 0; i <= availableSlots.Count - requiredSlots; i++)
        {
            var consecutiveSlots = availableSlots.Skip(i).Take(requiredSlots).ToList();
            
            if (AreConsecutiveSlots(consecutiveSlots) && 
                MeetsTaskRequirements(task, consecutiveSlots, context))
            {
                // Return a combined slot representing the full duration
                var firstSlot = consecutiveSlots.First();
                return new AvailableTimeSlot
                {
                    Date = firstSlot.Date,
                    StartTime = firstSlot.StartTime,
                    Duration = task.Duration,
                    PredictedEnergyLevel = (int)consecutiveSlots.Average(s => s.PredictedEnergyLevel),
                    Score = consecutiveSlots.Average(s => s.Score)
                };
            }
        }

        return null;
    }

    private bool AreConsecutiveSlots(List<AvailableTimeSlot> slots)
    {
        if (slots.Count <= 1) return true;

        for (int i = 1; i < slots.Count; i++)
        {
            var prevSlot = slots[i - 1];
            var currentSlot = slots[i];

            if (currentSlot.Date != prevSlot.Date ||
                currentSlot.StartTime != prevSlot.StartTime.Add(prevSlot.Duration))
            {
                return false;
            }
        }

        return true;
    }

    private bool MeetsTaskRequirements(SchedulingItem task, List<AvailableTimeSlot> slots, SchedulingContext context)
    {
        var averageEnergyLevel = (int)slots.Average(s => s.PredictedEnergyLevel);
        
        // Check if energy level meets requirements
        if (averageEnergyLevel < task.RequiredEnergyLevel - 2) // Allow 2-point tolerance
        {
            return false;
        }

        // Check preferred date
        if (task.PreferredDate.HasValue && 
            slots.First().Date != task.PreferredDate.Value.Date &&
            Math.Abs((slots.First().Date - task.PreferredDate.Value.Date).TotalDays) > 2) // Allow 2-day tolerance
        {
            return false;
        }

        return true;
    }

    private decimal CalculateSlotScore(TimeSpan timeOfDay, int energyLevel, SchedulingContext context)
    {
        decimal score = 0;

        // Energy level contribution (0-40 points)
        score += energyLevel * 4;

        // Peak hours bonus (0-30 points)
        if (context.Patterns.PeakHours.Any(h => Math.Abs((h - timeOfDay).TotalHours) < 1))
        {
            score += 30;
        }

        // Preference alignment (0-20 points)
        if (context.Preferences.PreferredStartTime.HasValue && context.Preferences.PreferredEndTime.HasValue)
        {
            if (timeOfDay >= context.Preferences.PreferredStartTime.Value && 
                timeOfDay <= context.Preferences.PreferredEndTime.Value)
            {
                score += 20;
            }
        }

        // Break time consideration (0-10 points)
        if (context.Preferences.MinimumBreakDuration.HasValue)
        {
            // Prefer slots that allow for breaks
            score += 10;
        }

        return Math.Min(100, score);
    }

    private void AddScheduledBreak(OptimizedSchedule schedule, ScheduledTask task, SchedulingPreferences preferences)
    {
        var breakDuration = preferences.MinimumBreakDuration ?? TimeSpan.FromMinutes(15);
        var breakStartTime = task.StartTime.Add(task.Duration);

        schedule.ScheduledBreaks.Add(new ScheduledBreak
        {
            StartTime = breakStartTime,
            Duration = breakDuration,
            Type = breakDuration.TotalMinutes <= 15 ? BreakType.Short : BreakType.Medium,
            Activity = "Rest and recharge"
        });
    }

    private bool TasksOverlap(ScheduledTask task1, ScheduledTask task2)
    {
        var task1End = task1.StartTime.Add(task1.Duration);
        var task2End = task2.StartTime.Add(task2.Duration);

        return task1.StartTime < task2End && task2.StartTime < task1End;
    }

    private bool IsWithinPreferredHours(ScheduledTask task, SchedulingPreferences preferences)
    {
        if (!preferences.PreferredStartTime.HasValue || !preferences.PreferredEndTime.HasValue)
            return true;

        var taskTime = task.StartTime.TimeOfDay;
        var taskEndTime = taskTime.Add(task.Duration);

        return taskTime >= preferences.PreferredStartTime.Value &&
               taskEndTime <= preferences.PreferredEndTime.Value;
    }

    private TimeSpan CalculateFreeTime(SchedulingContext context, OptimizedSchedule schedule)
    {
        var totalAvailableTime = (context.EndDate - context.StartDate).TotalHours * 
                                (context.Preferences.PreferredEndTime?.TotalHours - context.Preferences.PreferredStartTime?.TotalHours ?? 8);
        
        var totalScheduledTime = schedule.ScheduledTasks.Sum(t => t.Duration.TotalHours) +
                                schedule.ScheduledBreaks.Sum(b => b.Duration.TotalHours);

        return TimeSpan.FromHours(Math.Max(0, totalAvailableTime - totalScheduledTime));
    }
}

public class CalendarIntegration : ICalendarIntegration
{
    private readonly ILogger<CalendarIntegration> _logger;

    public CalendarIntegration(ILogger<CalendarIntegration> logger)
    {
        _logger = logger;
    }

    public async Task<List<CalendarCommitment>> GetCommitmentsAsync(string userId, DateTime startDate, DateTime endDate, CancellationToken cancellationToken = default)
    {
        // In a real implementation, this would integrate with calendar services (Google Calendar, Outlook, etc.)
        // For now, return sample commitments
        
        var commitments = new List<CalendarCommitment>();

        // Add some sample meetings and commitments
        var currentDate = startDate;
        while (currentDate <= endDate)
        {
            // Skip weekends
            if (currentDate.DayOfWeek != DayOfWeek.Saturday && currentDate.DayOfWeek != DayOfWeek.Sunday)
            {
                // Morning standup (common in many workplaces)
                if (Random.Shared.NextDouble() > 0.3) // 70% chance
                {
                    commitments.Add(new CalendarCommitment
                    {
                        Id = Guid.NewGuid().ToString(),
                        Title = "Daily Standup",
                        StartTime = currentDate.AddHours(9),
                        EndTime = currentDate.AddHours(9.5),
                        IsFlexible = false,
                        Location = "Conference Room / Video Call",
                        Attendees = new List<string> { "Team" }
                    });
                }

                // Lunch break
                commitments.Add(new CalendarCommitment
                {
                    Id = Guid.NewGuid().ToString(),
                    Title = "Lunch",
                    StartTime = currentDate.AddHours(12),
                    EndTime = currentDate.AddHours(13),
                    IsFlexible = true,
                    Location = "Cafeteria",
                    Attendees = new List<string>()
                });

                // Occasional afternoon meetings
                if (Random.Shared.NextDouble() > 0.6) // 40% chance
                {
                    commitments.Add(new CalendarCommitment
                    {
                        Id = Guid.NewGuid().ToString(),
                        Title = "Project Review Meeting",
                        StartTime = currentDate.AddHours(14),
                        EndTime = currentDate.AddHours(15),
                        IsFlexible = false,
                        Location = "Conference Room",
                        Attendees = new List<string> { "Manager", "Stakeholders" }
                    });
                }
            }

            currentDate = currentDate.AddDays(1);
        }

        _logger.LogInformation("Retrieved {CommitmentCount} calendar commitments for user {UserId}", 
            commitments.Count, userId);

        return commitments;
    }

    public async Task<bool> CreateEventAsync(string userId, ScheduledTask task, CancellationToken cancellationToken = default)
    {
        // In a real implementation, this would create events in the user's calendar
        _logger.LogInformation("Created calendar event for user {UserId}: {TaskTitle} at {StartTime}", 
            userId, task.Title, task.StartTime);
        
        return true; // Simulate successful creation
    }

    public async Task<bool> UpdateEventAsync(string userId, string eventId, ScheduledTask task, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Updated calendar event {EventId} for user {UserId}: {TaskTitle}", 
            eventId, userId, task.Title);
        
        return true; // Simulate successful update
    }

    public async Task<bool> DeleteEventAsync(string userId, string eventId, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Deleted calendar event {EventId} for user {UserId}", eventId, userId);
        
        return true; // Simulate successful deletion
    }

    public async Task<List<AvailableTimeSlot>> FindAvailableSlotAsync(string userId, TimeSpan duration, DateTime preferredDate, CancellationToken cancellationToken = default)
    {
        var commitments = await GetCommitmentsAsync(userId, preferredDate, preferredDate.AddDays(1), cancellationToken);
        var availableSlots = new List<AvailableTimeSlot>();

        var workStart = new TimeSpan(8, 0, 0);
        var workEnd = new TimeSpan(18, 0, 0);
        var currentTime = workStart;

        while (currentTime.Add(duration) <= workEnd)
        {
            var proposedStart = preferredDate.Add(currentTime);
            var proposedEnd = proposedStart.Add(duration);

            var hasConflict = commitments.Any(c => 
                c.StartTime < proposedEnd && c.EndTime > proposedStart);

            if (!hasConflict)
            {
                availableSlots.Add(new AvailableTimeSlot
                {
                    Date = preferredDate,
                    StartTime = currentTime,
                    Duration = duration,
                    PredictedEnergyLevel = GetPredictedEnergyLevel(currentTime),
                    Score = CalculateSlotScore(currentTime)
                });
            }

            currentTime = currentTime.Add(TimeSpan.FromMinutes(30));
        }

        return availableSlots.OrderByDescending(s => s.Score).ToList();
    }

    private int GetPredictedEnergyLevel(TimeSpan timeOfDay)
    {
        // Simple energy level prediction based on time of day
        var hour = timeOfDay.Hours;
        return hour switch
        {
            >= 8 and < 10 => 8,   // High morning energy
            >= 10 and < 12 => 7,  // Good morning energy
            >= 12 and < 14 => 5,  // Lunch dip
            >= 14 and < 16 => 7,  // Afternoon recovery
            >= 16 and < 18 => 6,  // Late afternoon
            _ => 4                // Outside normal hours
        };
    }

    private decimal CalculateSlotScore(TimeSpan timeOfDay)
    {
        var energyLevel = GetPredictedEnergyLevel(timeOfDay);
        var hour = timeOfDay.Hours;

        decimal score = energyLevel * 10; // Base score from energy level

        // Bonus for optimal work hours
        if (hour >= 9 && hour < 12 || hour >= 14 && hour < 16)
        {
            score += 20;
        }

        // Penalty for very early or late hours
        if (hour < 8 || hour > 17)
        {
            score -= 10;
        }

        return Math.Max(0, Math.Min(100, score));
    }
}