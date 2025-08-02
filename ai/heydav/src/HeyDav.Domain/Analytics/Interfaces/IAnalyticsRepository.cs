using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Common.Interfaces;

namespace HeyDav.Domain.Analytics.Interfaces;

public interface IProductivitySessionRepository : IRepository<ProductivitySession>
{
    Task<List<ProductivitySession>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivitySession>> GetByUserIdAndDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<ProductivitySession>> GetByTypeAsync(string userId, SessionType type, CancellationToken cancellationToken = default);
    Task<List<ProductivitySession>> GetActiveSessionsAsync(string userId, CancellationToken cancellationToken = default);
    Task<ProductivitySession?> GetCurrentActiveSessionAsync(string userId, CancellationToken cancellationToken = default);
    Task<Dictionary<DateTime, decimal>> GetDailyProductivityScoresAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<SessionType, TimeSpan>> GetTimeByTypeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}

public interface ITimeEntryRepository : IRepository<TimeEntry>
{
    Task<List<TimeEntry>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetByUserIdAndDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetByProjectAsync(string userId, string project, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetByCategoryAsync(string userId, string category, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetByTaskIdAsync(Guid taskId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetByGoalIdAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetActiveEntriesAsync(string userId, CancellationToken cancellationToken = default);
    Task<TimeEntry?> GetCurrentActiveEntryAsync(string userId, CancellationToken cancellationToken = default);
    Task<Dictionary<string, TimeSpan>> GetTimeByProjectAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<string, TimeSpan>> GetTimeByCategoryAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetBillableEntriesAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}

public interface IProductivityReportRepository : IRepository<ProductivityReport>
{
    Task<List<ProductivityReport>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityReport>> GetByUserIdAndTypeAsync(string userId, ReportType type, CancellationToken cancellationToken = default);
    Task<List<ProductivityReport>> GetByDateRangeAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ProductivityReport?> GetLatestReportAsync(string userId, ReportType type, CancellationToken cancellationToken = default);
    Task<List<ProductivityReport>> GetRecentReportsAsync(string userId, int count = 10, CancellationToken cancellationToken = default);
    Task<bool> ExistsForPeriodAsync(string userId, ReportType type, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}

public interface IAnalyticsDataRepository
{
    // Raw data aggregation methods
    Task<Dictionary<DateTime, decimal>> GetDailyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<DateTime, decimal>> GetWeeklyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<DateTime, decimal>> GetMonthlyMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Time-based aggregations
    Task<Dictionary<TimeSpan, decimal>> GetHourlyAveragesAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<DayOfWeek, decimal>> GetDayOfWeekAveragesAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Statistical operations
    Task<decimal> GetMetricAverageAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<decimal> GetMetricSumAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<decimal> GetMetricMaxAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<decimal> GetMetricMinAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Correlation analysis
    Task<decimal> GetMetricCorrelationAsync(string userId, string metric1, string metric2, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // User comparison data
    Task<Dictionary<string, decimal>> GetUserBenchmarksAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
}