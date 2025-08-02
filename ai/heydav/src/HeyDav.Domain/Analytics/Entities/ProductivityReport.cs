using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Domain.Analytics.Entities;

public class ProductivityReport : BaseEntity
{
    public string UserId { get; private set; }
    public ReportType Type { get; private set; }
    public DateTime FromDate { get; private set; }
    public DateTime ToDate { get; private set; }
    public DateTime GeneratedAt { get; private set; }
    public ReportStatus Status { get; private set; }
    public string Title { get; private set; }
    public ProductivityScoreCard ScoreCard { get; private set; }
    public List<ProductivityInsight> Insights { get; private set; }
    public List<PerformanceMetric> Metrics { get; private set; }
    public List<ProductivityTrend> Trends { get; private set; }
    public List<string> Recommendations { get; private set; }
    public Dictionary<string, object> Data { get; private set; }
    public string? Summary { get; private set; }

    private ProductivityReport() 
    {
        UserId = string.Empty;
        Title = string.Empty;
        ScoreCard = ProductivityScoreCard.Empty();
        Insights = new List<ProductivityInsight>();
        Metrics = new List<PerformanceMetric>();
        Trends = new List<ProductivityTrend>();
        Recommendations = new List<string>();
        Data = new Dictionary<string, object>();
    }

    public ProductivityReport(
        string userId,
        ReportType type,
        DateTime fromDate,
        DateTime toDate,
        string title,
        ProductivityScoreCard scoreCard,
        List<ProductivityInsight>? insights = null,
        List<PerformanceMetric>? metrics = null,
        List<ProductivityTrend>? trends = null,
        List<string>? recommendations = null,
        Dictionary<string, object>? data = null,
        string? summary = null)
    {
        if (string.IsNullOrWhiteSpace(userId))
            throw new ArgumentException("User ID cannot be empty", nameof(userId));
        
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Title cannot be empty", nameof(title));
        
        if (fromDate >= toDate)
            throw new ArgumentException("From date must be before to date");

        UserId = userId;
        Type = type;
        FromDate = fromDate;
        ToDate = toDate;
        GeneratedAt = DateTime.UtcNow;
        Status = ReportStatus.Generated;
        Title = title;
        ScoreCard = scoreCard ?? throw new ArgumentNullException(nameof(scoreCard));
        Insights = insights ?? new List<ProductivityInsight>();
        Metrics = metrics ?? new List<PerformanceMetric>();
        Trends = trends ?? new List<ProductivityTrend>();
        Recommendations = recommendations ?? new List<string>();
        Data = data ?? new Dictionary<string, object>();
        Summary = summary;
    }

    public void AddInsight(ProductivityInsight insight)
    {
        if (insight != null && !Insights.Any(i => i.Id == insight.Id))
        {
            Insights.Add(insight);
        }
    }

    public void AddMetric(PerformanceMetric metric)
    {
        if (metric != null && !Metrics.Any(m => m.Name == metric.Name))
        {
            Metrics.Add(metric);
        }
    }

    public void AddTrend(ProductivityTrend trend)
    {
        if (trend != null)
        {
            Trends.Add(trend);
        }
    }

    public void AddRecommendation(string recommendation)
    {
        if (!string.IsNullOrWhiteSpace(recommendation) && !Recommendations.Contains(recommendation))
        {
            Recommendations.Add(recommendation);
        }
    }

    public void UpdateSummary(string summary)
    {
        Summary = summary;
    }

    public void UpdateData(string key, object value)
    {
        if (!string.IsNullOrWhiteSpace(key))
        {
            Data[key] = value;
        }
    }

    public void MarkAsDelivered()
    {
        Status = ReportStatus.Delivered;
    }

    public void MarkAsArchived()
    {
        Status = ReportStatus.Archived;
    }

    public void MarkAsError(string errorMessage)
    {
        Status = ReportStatus.Error;
        UpdateData("error", errorMessage);
    }

    public TimeSpan GetReportPeriod()
    {
        return ToDate.Subtract(FromDate);
    }

    public bool IsRecent(TimeSpan threshold)
    {
        return DateTime.UtcNow.Subtract(GeneratedAt) <= threshold;
    }

    public List<ProductivityInsight> GetHighPriorityInsights()
    {
        return Insights.Where(i => i.Priority == InsightPriority.High || i.Priority == InsightPriority.Urgent)
                      .OrderByDescending(i => i.Priority)
                      .ToList();
    }

    public List<ProductivityInsight> GetActionableInsights()
    {
        return Insights.Where(i => i.IsActionable).ToList();
    }
}