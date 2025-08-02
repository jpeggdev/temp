using HeyDav.Application.Analytics.Models;
using HeyDav.Application.Analytics.Services;
using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.Interfaces;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Analytics.Services;

public class TimeTrackingSystem : ITimeTrackingSystem
{
    private readonly ILogger<TimeTrackingSystem> _logger;
    private readonly ITimeEntryRepository _timeEntryRepository;
    private readonly IAutomaticTimeTracker _automaticTracker;
    private readonly IFocusTracker _focusTracker;
    private readonly ITimeEstimationEngine _estimationEngine;

    public TimeTrackingSystem(
        ILogger<TimeTrackingSystem> logger,
        ITimeEntryRepository timeEntryRepository,
        IAutomaticTimeTracker automaticTracker,
        IFocusTracker focusTracker,
        ITimeEstimationEngine estimationEngine)
    {
        _logger = logger;
        _timeEntryRepository = timeEntryRepository;
        _automaticTracker = automaticTracker;
        _focusTracker = focusTracker;
        _estimationEngine = estimationEngine;
    }

    public async Task<TimeEntry> StartManualTrackingAsync(string userId, string activity, string? project = null, string? category = null, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Starting manual time tracking for user {UserId}, activity: {Activity}", userId, activity);

        // Stop any existing active tracking
        var activeEntry = await GetActiveTimeEntryAsync(userId, cancellationToken);
        if (activeEntry != null)
        {
            await StopTrackingAsync(activeEntry.Id, cancellationToken);
        }

        var timeEntry = new TimeEntry(
            userId,
            DateTime.UtcNow,
            activity,
            TimeTrackingSource.Manual,
            project,
            category,
            isManual: true
        );

        await _timeEntryRepository.AddAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Started time tracking for entry {EntryId}", timeEntry.Id);
        return timeEntry;
    }

    public async Task<TimeEntry> StopTrackingAsync(string userId, CancellationToken cancellationToken = default)
    {
        var activeEntry = await GetActiveTimeEntryAsync(userId, cancellationToken);
        if (activeEntry == null)
        {
            throw new InvalidOperationException("No active time tracking found for user");
        }

        return await StopTrackingAsync(activeEntry.Id, cancellationToken);
    }

    public async Task<TimeEntry> StopTrackingAsync(Guid timeEntryId, CancellationToken cancellationToken = default)
    {
        var timeEntry = await _timeEntryRepository.GetByIdAsync(timeEntryId, cancellationToken);
        if (timeEntry == null)
        {
            throw new ArgumentException($"Time entry {timeEntryId} not found");
        }

        if (!timeEntry.IsActive)
        {
            throw new InvalidOperationException("Time entry is not active");
        }

        timeEntry.Stop(DateTime.UtcNow);
        await _timeEntryRepository.UpdateAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Stopped time tracking for entry {EntryId}, duration: {Duration}", 
            timeEntry.Id, timeEntry.Duration);

        return timeEntry;
    }

    public async Task<TimeEntry> CreateTimeEntryAsync(string userId, DateTime startTime, DateTime endTime, string activity, string? project = null, string? category = null, CancellationToken cancellationToken = default)
    {
        if (startTime >= endTime)
        {
            throw new ArgumentException("Start time must be before end time");
        }

        var timeEntry = new TimeEntry(
            userId,
            startTime,
            activity,
            TimeTrackingSource.Manual,
            project,
            category,
            isManual: true
        );

        timeEntry.Stop(endTime);

        await _timeEntryRepository.AddAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Created time entry {EntryId} for user {UserId}, duration: {Duration}", 
            timeEntry.Id, userId, timeEntry.Duration);

        return timeEntry;
    }

    public async Task<TimeEntry> UpdateTimeEntryAsync(Guid timeEntryId, TimeEntryUpdateRequest request, CancellationToken cancellationToken = default)
    {
        var timeEntry = await _timeEntryRepository.GetByIdAsync(timeEntryId, cancellationToken);
        if (timeEntry == null)
        {
            throw new ArgumentException($"Time entry {timeEntryId} not found");
        }

        if (request.Activity != null)
            timeEntry.UpdateActivity(request.Activity);

        if (request.Project != null)
            timeEntry.UpdateProject(request.Project);

        if (request.Category != null)
            timeEntry.UpdateCategory(request.Category);

        if (request.Description != null)
            timeEntry.UpdateDescription(request.Description);

        if (request.EndTime.HasValue)
            timeEntry.UpdateEndTime(request.EndTime.Value);

        if (request.IsBillable.HasValue)
        {
            if (request.IsBillable.Value)
                timeEntry.MarkAsBillable(request.HourlyRate);
            else
                timeEntry.MarkAsNonBillable();
        }

        if (request.Tags != null)
        {
            // Update tags (simple implementation - clear and re-add)
            foreach (var tag in request.Tags)
            {
                timeEntry.AddTag(tag);
            }
        }

        await _timeEntryRepository.UpdateAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);

        return timeEntry;
    }

    public async Task DeleteTimeEntryAsync(Guid timeEntryId, CancellationToken cancellationToken = default)
    {
        var timeEntry = await _timeEntryRepository.GetByIdAsync(timeEntryId, cancellationToken);
        if (timeEntry == null)
        {
            throw new ArgumentException($"Time entry {timeEntryId} not found");
        }

        await _timeEntryRepository.DeleteAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Deleted time entry {EntryId}", timeEntryId);
    }

    public async Task StartAutomaticTrackingAsync(string userId, CancellationToken cancellationToken = default)
    {
        var settings = new AutomaticTrackingSettings(); // Use default settings
        await _automaticTracker.StartTrackingAsync(userId, settings, cancellationToken);
        _logger.LogInformation("Started automatic time tracking for user {UserId}", userId);
    }

    public async Task StopAutomaticTrackingAsync(string userId, CancellationToken cancellationToken = default)
    {
        await _automaticTracker.StopTrackingAsync(userId, cancellationToken);
        _logger.LogInformation("Stopped automatic time tracking for user {UserId}", userId);
    }

    public async Task<bool> IsAutomaticTrackingActiveAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await _automaticTracker.IsTrackingAsync(userId, cancellationToken);
    }

    public async Task ProcessAutomaticTrackingDataAsync(string userId, List<ActivityDetectionResult> activities, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Processing {Count} automatic tracking activities for user {UserId}", activities.Count, userId);

        foreach (var activity in activities)
        {
            var timeEntry = new TimeEntry(
                userId,
                activity.StartTime,
                activity.Activity,
                activity.Source,
                category: activity.Category,
                isManual: false,
                metadata: activity.Metadata
            );

            timeEntry.Stop(activity.EndTime);

            if (activity.Application != null)
                timeEntry.UpdateMetadata("application", activity.Application);
            if (activity.WindowTitle != null)
                timeEntry.UpdateMetadata("windowTitle", activity.WindowTitle);

            timeEntry.UpdateMetadata("confidence", activity.Confidence);

            await _timeEntryRepository.AddAsync(timeEntry, cancellationToken);
        }

        await _timeEntryRepository.SaveChangesAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetTimeEntriesAsync(string userId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        if (fromDate.HasValue && toDate.HasValue)
        {
            return await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate.Value, toDate.Value, cancellationToken);
        }

        return await _timeEntryRepository.GetByUserIdAsync(userId, cancellationToken);
    }

    public async Task<TimeEntry?> GetActiveTimeEntryAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await _timeEntryRepository.GetCurrentActiveEntryAsync(userId, cancellationToken);
    }

    public async Task<List<TimeEntry>> GetTimeEntriesByProjectAsync(string userId, string project, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByProjectAsync(userId, project, cancellationToken);
        
        if (fromDate.HasValue && toDate.HasValue)
        {
            entries = entries.Where(e => e.StartTime >= fromDate.Value && e.StartTime <= toDate.Value).ToList();
        }

        return entries;
    }

    public async Task<List<TimeEntry>> GetTimeEntriesByCategoryAsync(string userId, string category, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByCategoryAsync(userId, category, cancellationToken);
        
        if (fromDate.HasValue && toDate.HasValue)
        {
            entries = entries.Where(e => e.StartTime >= fromDate.Value && e.StartTime <= toDate.Value).ToList();
        }

        return entries;
    }

    public async Task<List<TimeEntry>> GetBillableTimeEntriesAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await _timeEntryRepository.GetBillableEntriesAsync(userId, fromDate, toDate, cancellationToken);
    }

    public async Task<TimeAllocationReport> GetTimeAllocationReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = entries.Where(e => e.Duration.HasValue).ToList();

        var report = new TimeAllocationReport
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            GeneratedAt = DateTime.UtcNow,
            TotalTrackedTime = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks))
        };

        // Time by category
        report.TimeByCategory = completedEntries
            .Where(e => !string.IsNullOrEmpty(e.Category))
            .GroupBy(e => e.Category!)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));

        // Time by project
        report.TimeByProject = completedEntries
            .Where(e => !string.IsNullOrEmpty(e.Project))
            .GroupBy(e => e.Project!)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));

        // Time by day of week
        report.TimeByDayOfWeek = completedEntries
            .GroupBy(e => e.StartTime.DayOfWeek)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));

        // Time by hour
        report.TimeByHour = completedEntries
            .GroupBy(e => e.StartTime.Hour)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));

        // Daily breakdowns
        report.DailyBreakdowns = completedEntries
            .GroupBy(e => e.StartTime.Date)
            .Select(g => new DailyTimeBreakdown
            {
                Date = g.Key,
                TotalTime = TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)),
                NumberOfSessions = g.Count(),
                AverageSessionDuration = TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks) / g.Count()),
                CategoryTime = g.Where(e => !string.IsNullOrEmpty(e.Category))
                              .GroupBy(e => e.Category!)
                              .ToDictionary(cg => cg.Key, cg => TimeSpan.FromTicks(cg.Sum(e => e.Duration!.Value.Ticks)))
            })
            .OrderBy(d => d.Date)
            .ToList();

        // Calculate productive time percentage (placeholder logic)
        var productiveCategories = new[] { "Development", "Writing", "Research", "Planning" };
        var productiveTime = completedEntries
            .Where(e => productiveCategories.Contains(e.Category))
            .Sum(e => e.Duration!.Value.TotalMinutes);
        
        report.ProductiveTimePercentage = report.TotalTrackedTime.TotalMinutes > 0 
            ? (decimal)(productiveTime / report.TotalTrackedTime.TotalMinutes * 100) 
            : 0;

        return report;
    }

    public async Task<ProjectTimeReport> GetProjectTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = entries.Where(e => e.Duration.HasValue && !string.IsNullOrEmpty(e.Project)).ToList();

        var report = new ProjectTimeReport
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            GeneratedAt = DateTime.UtcNow,
            TotalProjectTime = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks))
        };

        var projectGroups = completedEntries.GroupBy(e => e.Project!);

        report.Projects = projectGroups.Select(g =>
        {
            var projectEntries = g.ToList();
            var totalTime = TimeSpan.FromTicks(projectEntries.Sum(e => e.Duration!.Value.Ticks));

            return new ProjectTimeData
            {
                Project = g.Key,
                TotalTime = totalTime,
                Percentage = (decimal)(totalTime.TotalMinutes / report.TotalProjectTime.TotalMinutes * 100),
                SessionCount = projectEntries.Count,
                AverageSessionDuration = TimeSpan.FromTicks(projectEntries.Sum(e => e.Duration!.Value.Ticks) / projectEntries.Count),
                TopActivities = projectEntries.GroupBy(e => e.Activity).OrderByDescending(ag => ag.Count()).Take(3).Select(ag => ag.Key).ToList(),
                DailyTime = projectEntries.GroupBy(e => e.StartTime.Date).ToDictionary(dg => dg.Key, dg => TimeSpan.FromTicks(dg.Sum(e => e.Duration!.Value.Ticks)))
            };
        }).OrderByDescending(p => p.TotalTime).ToList();

        report.MostTimeConsuming = report.Projects.FirstOrDefault()?.Project ?? "";
        report.AverageHoursPerProject = report.Projects.Any() ? (decimal)report.Projects.Average(p => p.TotalTime.TotalHours) : 0;

        return report;
    }

    public async Task<CategoryTimeReport> GetCategoryTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = entries.Where(e => e.Duration.HasValue && !string.IsNullOrEmpty(e.Category)).ToList();

        var report = new CategoryTimeReport
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            GeneratedAt = DateTime.UtcNow,
            TotalCategorizedTime = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks))
        };

        var categoryGroups = completedEntries.GroupBy(e => e.Category!);

        report.Categories = categoryGroups.Select(g =>
        {
            var categoryEntries = g.ToList();
            var totalTime = TimeSpan.FromTicks(categoryEntries.Sum(e => e.Duration!.Value.Ticks));

            return new CategoryTimeData
            {
                Category = g.Key,
                TotalTime = totalTime,
                Percentage = (decimal)(totalTime.TotalMinutes / report.TotalCategorizedTime.TotalMinutes * 100),
                SessionCount = categoryEntries.Count,
                AverageSessionDuration = TimeSpan.FromTicks(categoryEntries.Sum(e => e.Duration!.Value.Ticks) / categoryEntries.Count),
                TopActivities = categoryEntries.GroupBy(e => e.Activity).OrderByDescending(ag => ag.Count()).Take(3).Select(ag => ag.Key).ToList(),
                ProductivityRating = GetCategoryProductivityRating(g.Key),
                PeakHours = GetCategoryPeakHours(categoryEntries)
            };
        }).OrderByDescending(c => c.TotalTime).ToList();

        report.MostTimeConsuming = report.Categories.FirstOrDefault()?.Category ?? "";
        report.ProductivityScore = CalculateOverallProductivityScore(report.Categories);

        return report;
    }

    public async Task<ProductivityTimeReport> GetProductivityTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = entries.Where(e => e.Duration.HasValue).ToList();

        var report = new ProductivityTimeReport
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            GeneratedAt = DateTime.UtcNow,
            TotalWorkTime = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks))
        };

        // Categorize time by productivity level
        var productiveCategories = new[] { "Development", "Writing", "Research", "Planning" };
        var neutralCategories = new[] { "Meetings", "Email", "Administrative" };
        var distractingCategories = new[] { "Social Media", "Entertainment", "Personal" };

        report.ProductiveTime = GetTimeByCategories(completedEntries, productiveCategories);
        report.NeutralTime = GetTimeByCategories(completedEntries, neutralCategories);
        report.DistractingTime = GetTimeByCategories(completedEntries, distractingCategories);

        // Calculate productivity score
        var totalMinutes = report.TotalWorkTime.TotalMinutes;
        if (totalMinutes > 0)
        {
            var productivePercentage = report.ProductiveTime.TotalMinutes / totalMinutes * 100;
            var neutralPercentage = report.NeutralTime.TotalMinutes / totalMinutes * 100;
            var distractingPercentage = report.DistractingTime.TotalMinutes / totalMinutes * 100;
            
            report.ProductivityScore = (decimal)(productivePercentage * 1.0 + neutralPercentage * 0.5 + distractingPercentage * 0.1);
        }

        // Hourly productivity scores
        report.HourlyProductivityScores = CalculateHourlyProductivityScores(completedEntries);

        // Identify peaks and dips
        report.ProductivityPeaks = IdentifyProductivityPeaks(report.HourlyProductivityScores, completedEntries);
        report.ProductivityDips = IdentifyProductivityDips(report.HourlyProductivityScores, completedEntries);

        // Generate insights
        report.Insights = GenerateProductivityInsights(report);

        return report;
    }

    public async Task<BillableTimeReport> GetBillableTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = entries.Where(e => e.Duration.HasValue).ToList();

        var billableEntries = completedEntries.Where(e => e.IsBillable).ToList();
        var nonBillableEntries = completedEntries.Where(e => !e.IsBillable).ToList();

        var report = new BillableTimeReport
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            GeneratedAt = DateTime.UtcNow,
            TotalBillableTime = TimeSpan.FromTicks(billableEntries.Sum(e => e.Duration!.Value.Ticks)),
            TotalNonBillableTime = TimeSpan.FromTicks(nonBillableEntries.Sum(e => e.Duration!.Value.Ticks)),
            TotalBillableAmount = billableEntries.Sum(e => e.CalculateBillableAmount() ?? 0)
        };

        report.AverageHourlyRate = report.TotalBillableTime.TotalHours > 0 
            ? report.TotalBillableAmount / (decimal)report.TotalBillableTime.TotalHours 
            : 0;

        // Project breakdown
        report.Projects = billableEntries
            .Where(e => !string.IsNullOrEmpty(e.Project))
            .GroupBy(e => e.Project!)
            .Select(g => new BillableProjectData
            {
                Project = g.Key,
                BillableTime = TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)),
                HourlyRate = g.Average(e => e.HourlyRate ?? 0),
                TotalAmount = g.Sum(e => e.CalculateBillableAmount() ?? 0),
                SessionCount = g.Count(),
                TimeEntries = g.Select(e => new BillableTimeEntry
                {
                    Id = e.Id,
                    StartTime = e.StartTime,
                    EndTime = e.EndTime!.Value,
                    Activity = e.Activity,
                    Duration = e.Duration!.Value,
                    HourlyRate = e.HourlyRate ?? 0,
                    Amount = e.CalculateBillableAmount() ?? 0
                }).ToList()
            })
            .OrderByDescending(p => p.TotalAmount)
            .ToList();

        // Daily earnings
        report.DailyEarnings = billableEntries
            .GroupBy(e => e.StartTime.Date)
            .ToDictionary(g => g.Key, g => g.Sum(e => e.CalculateBillableAmount() ?? 0));

        return report;
    }

    // Helper methods
    private decimal GetCategoryProductivityRating(string category)
    {
        return category.ToLower() switch
        {
            "development" => 9.0m,
            "writing" => 8.5m,
            "research" => 8.0m,
            "planning" => 7.5m,
            "meetings" => 6.0m,
            "email" => 5.0m,
            "administrative" => 4.0m,
            _ => 5.0m
        };
    }

    private List<TimeSpan> GetCategoryPeakHours(List<TimeEntry> entries)
    {
        return entries
            .GroupBy(e => new TimeSpan(e.StartTime.Hour, 0, 0))
            .OrderByDescending(g => g.Sum(e => e.Duration!.Value.Ticks))
            .Take(3)
            .Select(g => g.Key)
            .ToList();
    }

    private decimal CalculateOverallProductivityScore(List<CategoryTimeData> categories)
    {
        if (!categories.Any()) return 0;

        var weightedScore = categories
            .Sum(c => c.ProductivityRating * (decimal)c.TotalTime.TotalMinutes);
        
        var totalMinutes = categories.Sum(c => (decimal)c.TotalTime.TotalMinutes);
        
        return totalMinutes > 0 ? weightedScore / totalMinutes : 0;
    }

    private TimeSpan GetTimeByCategories(List<TimeEntry> entries, string[] categories)
    {
        var relevantEntries = entries.Where(e => categories.Contains(e.Category, StringComparer.OrdinalIgnoreCase));
        return TimeSpan.FromTicks(relevantEntries.Sum(e => e.Duration!.Value.Ticks));
    }

    private Dictionary<TimeSpan, decimal> CalculateHourlyProductivityScores(List<TimeEntry> entries)
    {
        var hourlyScores = new Dictionary<TimeSpan, decimal>();
        
        var hourlyGroups = entries.GroupBy(e => new TimeSpan(e.StartTime.Hour, 0, 0));
        
        foreach (var group in hourlyGroups)
        {
            var hourEntries = group.ToList();
            var productivityScore = CalculateHourlyProductivity(hourEntries);
            hourlyScores[group.Key] = productivityScore;
        }

        return hourlyScores;
    }

    private decimal CalculateHourlyProductivity(List<TimeEntry> entries)
    {
        // Simple productivity calculation based on category ratings
        var totalMinutes = entries.Sum(e => e.Duration!.Value.TotalMinutes);
        if (totalMinutes == 0) return 0;

        var weightedProductivity = entries.Sum(e => 
            GetCategoryProductivityRating(e.Category ?? "unknown") * e.Duration!.Value.TotalMinutes);

        return (decimal)(weightedProductivity / totalMinutes);
    }

    private List<ProductivityPeak> IdentifyProductivityPeaks(Dictionary<TimeSpan, decimal> hourlyScores, List<TimeEntry> entries)
    {
        var peaks = new List<ProductivityPeak>();
        var averageScore = hourlyScores.Values.Average();
        var threshold = averageScore * 1.2m; // 20% above average

        foreach (var hour in hourlyScores.Where(h => h.Value >= threshold))
        {
            var hourEntries = entries.Where(e => e.StartTime.Hour == hour.Key.Hours).ToList();
            var primaryActivity = hourEntries.GroupBy(e => e.Activity)
                                           .OrderByDescending(g => g.Count())
                                           .FirstOrDefault()?.Key ?? "Unknown";

            peaks.Add(new ProductivityPeak
            {
                StartTime = hour.Key,
                EndTime = hour.Key.Add(TimeSpan.FromHours(1)),
                AverageScore = hour.Value,
                PrimaryActivity = primaryActivity,
                Frequency = CalculateHourFrequency(hour.Key, entries)
            });
        }

        return peaks;
    }

    private List<ProductivityDip> IdentifyProductivityDips(Dictionary<TimeSpan, decimal> hourlyScores, List<TimeEntry> entries)
    {
        var dips = new List<ProductivityDip>();
        var averageScore = hourlyScores.Values.Average();
        var threshold = averageScore * 0.8m; // 20% below average

        foreach (var hour in hourlyScores.Where(h => h.Value <= threshold))
        {
            var hourEntries = entries.Where(e => e.StartTime.Hour == hour.Key.Hours).ToList();
            var primaryActivity = hourEntries.GroupBy(e => e.Activity)
                                           .OrderByDescending(g => g.Count())
                                           .FirstOrDefault()?.Key ?? "Unknown";

            dips.Add(new ProductivityDip
            {
                StartTime = hour.Key,
                EndTime = hour.Key.Add(TimeSpan.FromHours(1)),
                AverageScore = hour.Value,
                PrimaryActivity = primaryActivity,
                PossibleCauses = GetPossibleCauses(hour.Key, primaryActivity)
            });
        }

        return dips;
    }

    private int CalculateHourFrequency(TimeSpan hour, List<TimeEntry> entries)
    {
        var dayCount = entries.GroupBy(e => e.StartTime.Date).Count();
        var hourOccurrences = entries.Count(e => e.StartTime.Hour == hour.Hours);
        return dayCount > 0 ? hourOccurrences / dayCount : 0;
    }

    private List<string> GetPossibleCauses(TimeSpan hour, string primaryActivity)
    {
        var causes = new List<string>();
        
        if (hour.Hours >= 12 && hour.Hours <= 14)
            causes.Add("Post-lunch energy dip");
        
        if (hour.Hours >= 15 && hour.Hours <= 17)
            causes.Add("Afternoon fatigue");
        
        if (primaryActivity.ToLower().Contains("meeting"))
            causes.Add("Meeting-heavy period");
        
        if (primaryActivity.ToLower().Contains("email"))
            causes.Add("Administrative tasks");

        return causes;
    }

    private List<string> GenerateProductivityInsights(ProductivityTimeReport report)
    {
        var insights = new List<string>();

        if (report.ProductivityScore >= 80)
            insights.Add("Excellent productivity levels maintained throughout the period");
        else if (report.ProductivityScore >= 60)
            insights.Add("Good productivity with room for improvement");
        else
            insights.Add("Productivity levels below optimal - consider reviewing time allocation");

        if (report.ProductivityPeaks.Any())
        {
            var topPeak = report.ProductivityPeaks.OrderByDescending(p => p.AverageScore).First();
            insights.Add($"Peak productivity typically occurs around {topPeak.StartTime:hh\\:mm} - consider scheduling important work during this time");
        }

        if (report.ProductivityDips.Any())
        {
            var worstDip = report.ProductivityDips.OrderBy(d => d.AverageScore).First();
            insights.Add($"Productivity dips around {worstDip.StartTime:hh\\:mm} - consider breaks or lighter tasks during this period");
        }

        var productivePercentage = report.TotalWorkTime.TotalMinutes > 0 
            ? report.ProductiveTime.TotalMinutes / report.TotalWorkTime.TotalMinutes * 100 
            : 0;

        if (productivePercentage >= 70)
            insights.Add("Strong focus on high-value activities");
        else if (productivePercentage <= 40)
            insights.Add("Consider reducing time spent on low-value activities");

        return insights;
    }

    // Placeholder implementations for remaining interface methods
    public async Task<TimeEstimationInsight> GetTimeEstimationInsightAsync(string userId, string activity, CancellationToken cancellationToken = default)
    {
        return await _estimationEngine.GetEstimationInsightsAsync(userId, cancellationToken)
                                     .ContinueWith(t => t.Result.FirstOrDefault(i => i.Activity == activity) 
                                                      ?? new TimeEstimationInsight { UserId = userId, Activity = activity });
    }

    public async Task<List<TimeImprovementSuggestion>> GetTimeImprovementSuggestionsAsync(string userId, CancellationToken cancellationToken = default)
    {
        // Generate suggestions based on time tracking data
        var suggestions = new List<TimeImprovementSuggestion>();
        
        var recentEntries = await GetTimeEntriesAsync(userId, DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);
        
        // Check for context switching
        var dailyContextSwitches = recentEntries
            .GroupBy(e => e.StartTime.Date)
            .Average(g => g.GroupBy(e => e.Category).Count());
            
        if (dailyContextSwitches > 8)
        {
            suggestions.Add(new TimeImprovementSuggestion
            {
                Title = "Reduce Context Switching",
                Description = "You're switching between different types of work frequently, which can reduce efficiency",
                Category = "Focus",
                PotentialTimeSavings = 2.5m,
                ImplementationDifficulty = 6m,
                ActionSteps = new List<string> 
                { 
                    "Batch similar tasks together", 
                    "Schedule specific time blocks for different work types",
                    "Use time blocking techniques"
                },
                ExpectedResults = TimeSpan.FromWeeks(2)
            });
        }

        return suggestions;
    }

    public async Task UpdateTimeEstimationModelAsync(string userId, string activity, TimeSpan actualTime, TimeSpan estimatedTime, CancellationToken cancellationToken = default)
    {
        await _estimationEngine.UpdateEstimationModelAsync(userId, activity, actualTime, estimatedTime, cancellationToken);
    }

    public async Task<FocusSession> StartFocusSessionAsync(string userId, string activity, TimeSpan plannedDuration, CancellationToken cancellationToken = default)
    {
        var request = new FocusSessionRequest
        {
            Activity = activity,
            PlannedDuration = plannedDuration
        };

        return await _focusTracker.StartFocusSessionAsync(userId, request, cancellationToken);
    }

    public async Task<FocusSession> EndFocusSessionAsync(Guid focusSessionId, int focusScore, int interruptionCount, CancellationToken cancellationToken = default)
    {
        var completion = new FocusSessionCompletion
        {
            FocusScore = focusScore,
            CompletedSuccessfully = focusScore >= 7,
            Interruptions = new List<Interruption>() // Would be populated with actual interruption data
        };

        return await _focusTracker.EndFocusSessionAsync(focusSessionId, completion, cancellationToken);
    }

    public async Task<List<FocusSession>> GetFocusSessionsAsync(string userId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        return await _focusTracker.GetFocusSessionsAsync(userId, fromDate, toDate, cancellationToken);
    }

    public async Task<FocusMetrics> GetFocusMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await _focusTracker.CalculateFocusMetricsAsync(userId, fromDate, toDate, cancellationToken);
    }

    public async Task LinkTimeEntryToTaskAsync(Guid timeEntryId, Guid taskId, CancellationToken cancellationToken = default)
    {
        var timeEntry = await _timeEntryRepository.GetByIdAsync(timeEntryId, cancellationToken);
        if (timeEntry == null)
        {
            throw new ArgumentException($"Time entry {timeEntryId} not found");
        }

        timeEntry.UpdateMetadata("taskId", taskId);
        await _timeEntryRepository.UpdateAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);
    }

    public async Task LinkTimeEntryToGoalAsync(Guid timeEntryId, Guid goalId, CancellationToken cancellationToken = default)
    {
        var timeEntry = await _timeEntryRepository.GetByIdAsync(timeEntryId, cancellationToken);
        if (timeEntry == null)
        {
            throw new ArgumentException($"Time entry {timeEntryId} not found");
        }

        timeEntry.UpdateMetadata("goalId", goalId);
        await _timeEntryRepository.UpdateAsync(timeEntry, cancellationToken);
        await _timeEntryRepository.SaveChangesAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetTimeEntriesForTaskAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        return await _timeEntryRepository.GetByTaskIdAsync(taskId, cancellationToken);
    }

    public async Task<List<TimeEntry>> GetTimeEntriesForGoalAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        return await _timeEntryRepository.GetByGoalIdAsync(goalId, cancellationToken);
    }

    public async Task<TaskTimeAnalysis> GetTaskTimeAnalysisAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        var timeEntries = await GetTimeEntriesForTaskAsync(taskId, cancellationToken);
        var completedEntries = timeEntries.Where(e => e.Duration.HasValue).ToList();

        var analysis = new TaskTimeAnalysis
        {
            TaskId = taskId,
            TaskName = "Task Name", // Would be fetched from task repository
            TimeEntries = completedEntries,
            TotalTimeSpent = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks)),
            SessionCount = completedEntries.Count
        };

        if (completedEntries.Any())
        {
            analysis.FirstSession = completedEntries.Min(e => e.StartTime);
            analysis.LastSession = completedEntries.Max(e => e.StartTime);
            analysis.AverageSessionDuration = TimeSpan.FromTicks(
                completedEntries.Sum(e => e.Duration!.Value.Ticks) / completedEntries.Count);
        }

        // Generate insights
        if (analysis.SessionCount > 10)
            analysis.Insights.Add("Task required multiple sessions - consider breaking into smaller subtasks");
        
        if (analysis.AverageSessionDuration > TimeSpan.FromHours(2))
            analysis.Insights.Add("Long average session duration - consider taking breaks to maintain focus");

        return analysis;
    }

    public async Task<GoalTimeAnalysis> GetGoalTimeAnalysisAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        var timeEntries = await GetTimeEntriesForGoalAsync(goalId, cancellationToken);
        var completedEntries = timeEntries.Where(e => e.Duration.HasValue).ToList();

        var analysis = new GoalTimeAnalysis
        {
            GoalId = goalId,
            GoalName = "Goal Name", // Would be fetched from goal repository
            AllTimeEntries = completedEntries,
            TotalTimeSpent = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks))
        };

        if (completedEntries.Any())
        {
            analysis.FirstSession = completedEntries.Min(e => e.StartTime);
            analysis.LastSession = completedEntries.Max(e => e.StartTime);
            
            // Time by category
            analysis.TimeByCategory = completedEntries
                .Where(e => !string.IsNullOrEmpty(e.Category))
                .GroupBy(e => e.Category!)
                .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));
        }

        // Calculate velocity score (placeholder)
        analysis.VelocityScore = 75m; // Would be calculated based on progress vs time spent

        return analysis;
    }
}