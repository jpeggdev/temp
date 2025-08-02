using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.Interfaces;
using HeyDav.Infrastructure.Persistence;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Analytics.Repositories;

public class ProductivitySessionRepository : Repository<ProductivitySession>, IProductivitySessionRepository
{
    public ProductivitySessionRepository(HeyDavDbContext context) : base(context) { }

    public async Task<List<ProductivitySession>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(s => s.UserId == userId && !s.IsDeleted)
            .OrderByDescending(s => s.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<ProductivitySession>> GetByUserIdAndDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       !s.IsDeleted)
            .OrderBy(s => s.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<ProductivitySession>> GetByTypeAsync(string userId, SessionType type, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(s => s.UserId == userId && s.Type == type && !s.IsDeleted)
            .OrderByDescending(s => s.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<ProductivitySession>> GetActiveSessionsAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(s => s.UserId == userId && s.EndTime == null && !s.IsDeleted)
            .OrderBy(s => s.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<ProductivitySession?> GetCurrentActiveSessionAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(s => s.UserId == userId && s.EndTime == null && !s.IsDeleted)
            .OrderByDescending(s => s.StartTime)
            .FirstOrDefaultAsync(cancellationToken);
    }

    public async Task<Dictionary<DateTime, decimal>> GetDailyProductivityScoresAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await DbSet
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       s.EndTime != null &&
                       !s.IsDeleted)
            .ToListAsync(cancellationToken);

        return sessions
            .GroupBy(s => s.StartTime.Date)
            .ToDictionary(g => g.Key, g => g.Average(s => s.GetProductivityScore()));
    }

    public async Task<Dictionary<SessionType, TimeSpan>> GetTimeByTypeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await DbSet
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       s.Duration != null &&
                       !s.IsDeleted)
            .ToListAsync(cancellationToken);

        return sessions
            .GroupBy(s => s.Type)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(s => s.Duration!.Value.Ticks)));
    }
}

public class TimeEntryRepository : Repository<TimeEntry>, ITimeEntryRepository
{
    public TimeEntryRepository(HeyDavDbContext context) : base(context) { }

    public async Task<List<TimeEntry>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && !e.IsDeleted)
            .OrderByDescending(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetByUserIdAndDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && 
                       e.StartTime >= fromDate && 
                       e.StartTime <= toDate && 
                       !e.IsDeleted)
            .OrderBy(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetByProjectAsync(string userId, string project, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && e.Project == project && !e.IsDeleted)
            .OrderByDescending(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetByCategoryAsync(string userId, string category, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && e.Category == category && !e.IsDeleted)
            .OrderByDescending(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetByTaskIdAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.TaskId == taskId && !e.IsDeleted)
            .OrderBy(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetByGoalIdAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.GoalId == goalId && !e.IsDeleted)
            .OrderBy(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<TimeEntry>> GetActiveEntriesAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && e.EndTime == null && !e.IsDeleted)
            .OrderBy(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }

    public async Task<TimeEntry?> GetCurrentActiveEntryAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && e.EndTime == null && !e.IsDeleted)
            .OrderByDescending(e => e.StartTime)
            .FirstOrDefaultAsync(cancellationToken);
    }

    public async Task<Dictionary<string, TimeSpan>> GetTimeByProjectAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await DbSet
            .Where(e => e.UserId == userId && 
                       e.StartTime >= fromDate && 
                       e.StartTime <= toDate && 
                       e.Duration != null &&
                       e.Project != null &&
                       !e.IsDeleted)
            .ToListAsync(cancellationToken);

        return entries
            .GroupBy(e => e.Project!)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));
    }

    public async Task<Dictionary<string, TimeSpan>> GetTimeByCategoryAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var entries = await DbSet
            .Where(e => e.UserId == userId && 
                       e.StartTime >= fromDate && 
                       e.StartTime <= toDate && 
                       e.Duration != null &&
                       e.Category != null &&
                       !e.IsDeleted)
            .ToListAsync(cancellationToken);

        return entries
            .GroupBy(e => e.Category!)
            .ToDictionary(g => g.Key, g => TimeSpan.FromTicks(g.Sum(e => e.Duration!.Value.Ticks)));
    }

    public async Task<List<TimeEntry>> GetBillableEntriesAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(e => e.UserId == userId && 
                       e.StartTime >= fromDate && 
                       e.StartTime <= toDate && 
                       e.IsBillable &&
                       !e.IsDeleted)
            .OrderBy(e => e.StartTime)
            .ToListAsync(cancellationToken);
    }
}

public class ProductivityReportRepository : Repository<ProductivityReport>, IProductivityReportRepository
{
    public ProductivityReportRepository(HeyDavDbContext context) : base(context) { }

    public async Task<List<ProductivityReport>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(r => r.UserId == userId && !r.IsDeleted)
            .OrderByDescending(r => r.GeneratedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<ProductivityReport>> GetByUserIdAndTypeAsync(string userId, ReportType type, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(r => r.UserId == userId && r.Type == type && !r.IsDeleted)
            .OrderByDescending(r => r.GeneratedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<ProductivityReport>> GetByDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(r => r.UserId == userId && 
                       r.FromDate >= fromDate && 
                       r.ToDate <= toDate && 
                       !r.IsDeleted)
            .OrderByDescending(r => r.GeneratedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<ProductivityReport?> GetLatestReportAsync(string userId, ReportType type, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(r => r.UserId == userId && r.Type == type && !r.IsDeleted)
            .OrderByDescending(r => r.GeneratedAt)
            .FirstOrDefaultAsync(cancellationToken);
    }

    public async Task<List<ProductivityReport>> GetRecentReportsAsync(string userId, int count = 10, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .Where(r => r.UserId == userId && !r.IsDeleted)
            .OrderByDescending(r => r.GeneratedAt)
            .Take(count)
            .ToListAsync(cancellationToken);
    }

    public async Task<bool> ExistsForPeriodAsync(string userId, ReportType type, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        return await DbSet
            .AnyAsync(r => r.UserId == userId && 
                          r.Type == type && 
                          r.FromDate == fromDate && 
                          r.ToDate == toDate && 
                          !r.IsDeleted, 
                     cancellationToken);
    }
}

public class AnalyticsDataRepository : IAnalyticsDataRepository
{
    private readonly HeyDavDbContext _context;
    private readonly ILogger<AnalyticsDataRepository> _logger;

    public AnalyticsDataRepository(HeyDavDbContext context, ILogger<AnalyticsDataRepository> logger)
    {
        _context = context;
        _logger = logger;
    }

    public async Task<Dictionary<DateTime, decimal>> GetDailyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        // This would integrate with actual metric storage
        // For now, generating sample data based on sessions
        var sessions = await _context.ProductivitySessions
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       !s.IsDeleted)
            .ToListAsync(cancellationToken);

        return sessions
            .GroupBy(s => s.StartTime.Date)
            .ToDictionary(g => g.Key, g => GetMetricValueFromSessions(metricName, g.ToList()));
    }

    public async Task<Dictionary<DateTime, decimal>> GetWeeklyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        
        return dailyData
            .GroupBy(kvp => GetWeekStart(kvp.Key))
            .ToDictionary(g => g.Key, g => g.Average(kvp => kvp.Value));
    }

    public async Task<Dictionary<DateTime, decimal>> GetMonthlyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        
        return dailyData
            .GroupBy(kvp => new DateTime(kvp.Key.Year, kvp.Key.Month, 1))
            .ToDictionary(g => g.Key, g => g.Average(kvp => kvp.Value));
    }

    public async Task<Dictionary<TimeSpan, decimal>> GetHourlyAveragesAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _context.ProductivitySessions
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       !s.IsDeleted)
            .ToListAsync(cancellationToken);

        return sessions
            .GroupBy(s => new TimeSpan(s.StartTime.Hour, 0, 0))
            .ToDictionary(g => g.Key, g => GetMetricValueFromSessions(metricName, g.ToList()));
    }

    public async Task<Dictionary<DayOfWeek, decimal>> GetDayOfWeekAveragesAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _context.ProductivitySessions
            .Where(s => s.UserId == userId && 
                       s.StartTime >= fromDate && 
                       s.StartTime <= toDate && 
                       !s.IsDeleted)
            .ToListAsync(cancellationToken);

        return sessions
            .GroupBy(s => s.StartTime.DayOfWeek)
            .ToDictionary(g => g.Key, g => GetMetricValueFromSessions(metricName, g.ToList()));
    }

    public async Task<decimal> GetMetricAverageAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        return dailyData.Values.Any() ? dailyData.Values.Average() : 0;
    }

    public async Task<decimal> GetMetricSumAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        return dailyData.Values.Sum();
    }

    public async Task<decimal> GetMetricMaxAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        return dailyData.Values.Any() ? dailyData.Values.Max() : 0;
    }

    public async Task<decimal> GetMetricMinAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var dailyData = await GetDailyMetricAsync(userId, metricName, fromDate, toDate, cancellationToken);
        return dailyData.Values.Any() ? dailyData.Values.Min() : 0;
    }

    public async Task<decimal> GetMetricCorrelationAsync(string userId, string metric1, string metric2, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var data1 = await GetDailyMetricAsync(userId, metric1, fromDate, toDate, cancellationToken);
        var data2 = await GetDailyMetricAsync(userId, metric2, fromDate, toDate, cancellationToken);

        var commonDates = data1.Keys.Intersect(data2.Keys).ToList();
        if (commonDates.Count < 3) return 0;

        var values1 = commonDates.Select(d => (double)data1[d]).ToList();
        var values2 = commonDates.Select(d => (double)data2[d]).ToList();

        return (decimal)CalculatePearsonCorrelation(values1, values2);
    }

    public async Task<Dictionary<string, decimal>> GetUserBenchmarksAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default)
    {
        var benchmarks = new Dictionary<string, decimal>();
        
        foreach (var metricName in metricNames)
        {
            // This would calculate benchmarks from aggregated user data
            // For now, returning placeholder values
            benchmarks[metricName] = metricName switch
            {
                "ProductivityScore" => 75m,
                "TaskCompletionRate" => 80m,
                "EnergyLevel" => 7m,
                "FocusTime" => 4.2m,
                _ => 50m
            };
        }

        return benchmarks;
    }

    // Helper methods
    private decimal GetMetricValueFromSessions(string metricName, List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 0;

        return metricName switch
        {
            "ProductivityScore" => sessions.Average(s => s.GetProductivityScore()),
            "EnergyLevel" => (decimal)sessions.Average(s => s.EnergyLevelStart),
            "FocusTime" => (decimal)sessions.Where(s => s.Duration.HasValue && s.FocusScore >= 7)
                                   .Sum(s => s.Duration!.Value.TotalHours),
            "TaskCompletionRate" => sessions.Count(s => s.EndTime.HasValue) / (decimal)sessions.Count * 100,
            "InterruptionCount" => (decimal)sessions.Average(s => s.InterruptionCount),
            _ => 0
        };
    }

    private DateTime GetWeekStart(DateTime date)
    {
        var daysFromMonday = ((int)date.DayOfWeek - 1 + 7) % 7;
        return date.AddDays(-daysFromMonday).Date;
    }

    private double CalculatePearsonCorrelation(List<double> x, List<double> y)
    {
        if (x.Count != y.Count || x.Count < 2) return 0;

        var meanX = x.Average();
        var meanY = y.Average();

        var numerator = x.Zip(y, (xi, yi) => (xi - meanX) * (yi - meanY)).Sum();
        var denominator = Math.Sqrt(x.Sum(xi => Math.Pow(xi - meanX, 2)) * y.Sum(yi => Math.Pow(yi - meanY, 2)));

        return denominator == 0 ? 0 : numerator / denominator;
    }
}