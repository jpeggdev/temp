using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class ProductivitySessionConfiguration : IEntityTypeConfiguration<ProductivitySession>
{
    public void Configure(EntityTypeBuilder<ProductivitySession> builder)
    {
        builder.ToTable("ProductivitySessions");

        builder.HasKey(s => s.Id);

        builder.Property(s => s.UserId)
            .IsRequired()
            .HasMaxLength(450);

        builder.Property(s => s.StartTime)
            .IsRequired();

        builder.Property(s => s.EndTime);

        builder.Property(s => s.Type)
            .HasConversion<string>()
            .HasMaxLength(50);

        builder.Property(s => s.Context)
            .HasMaxLength(200);

        builder.Property(s => s.Description)
            .HasMaxLength(1000);

        builder.Property(s => s.EnergyLevelStart)
            .IsRequired();

        builder.Property(s => s.EnergyLevelEnd);

        builder.Property(s => s.MoodStart);

        builder.Property(s => s.MoodEnd);

        builder.Property(s => s.FocusScore);

        builder.Property(s => s.InterruptionCount)
            .HasDefaultValue(0);

        // Configure complex type as JSON
        builder.OwnsOne(s => s.Metrics, metricsBuilder =>
        {
            metricsBuilder.ToJson();
            metricsBuilder.Property(m => m.TasksCompleted);
            metricsBuilder.Property(m => m.FocusTime);
            metricsBuilder.Property(m => m.InterruptionCount);
            metricsBuilder.Property(m => m.ContextSwitches);
            metricsBuilder.Property(m => m.DeepWorkTime);
            metricsBuilder.Property(m => m.CustomMetrics)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, decimal>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, decimal>());
        });

        builder.Property(s => s.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnType("json");

        builder.Property(s => s.Metadata)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnType("json");

        // Base entity properties
        builder.Property(s => s.CreatedAt)
            .IsRequired();

        builder.Property(s => s.UpdatedAt)
            .IsRequired();

        builder.Property(s => s.IsDeleted)
            .HasDefaultValue(false);

        builder.Property(s => s.DeletedAt);

        // Indexes
        builder.HasIndex(s => s.UserId);
        builder.HasIndex(s => s.StartTime);
        builder.HasIndex(s => new { s.UserId, s.StartTime });
        builder.HasIndex(s => new { s.UserId, s.Type });
        builder.HasIndex(s => s.IsDeleted);

        // Global query filter
        builder.HasQueryFilter(s => !s.IsDeleted);
    }
}

public class TimeEntryConfiguration : IEntityTypeConfiguration<TimeEntry>
{
    public void Configure(EntityTypeBuilder<TimeEntry> builder)
    {
        builder.ToTable("TimeEntries");

        builder.HasKey(e => e.Id);

        builder.Property(e => e.UserId)
            .IsRequired()
            .HasMaxLength(450);

        builder.Property(e => e.StartTime)
            .IsRequired();

        builder.Property(e => e.EndTime);

        builder.Property(e => e.Activity)
            .IsRequired()
            .HasMaxLength(500);

        builder.Property(e => e.Project)
            .HasMaxLength(200);

        builder.Property(e => e.Category)
            .HasMaxLength(100);

        builder.Property(e => e.Description)
            .HasMaxLength(2000);

        builder.Property(e => e.IsManual)
            .HasDefaultValue(true);

        builder.Property(e => e.Source)
            .HasConversion<string>()
            .HasMaxLength(50);

        builder.Property(e => e.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnType("json");

        builder.Property(e => e.Metadata)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnType("json");

        builder.Property(e => e.TaskId);

        builder.Property(e => e.GoalId);

        builder.Property(e => e.IsBillable)
            .HasDefaultValue(false);

        builder.Property(e => e.HourlyRate)
            .HasPrecision(18, 2);

        // Base entity properties
        builder.Property(e => e.CreatedAt)
            .IsRequired();

        builder.Property(e => e.UpdatedAt)
            .IsRequired();

        builder.Property(e => e.IsDeleted)
            .HasDefaultValue(false);

        builder.Property(e => e.DeletedAt);

        // Indexes
        builder.HasIndex(e => e.UserId);
        builder.HasIndex(e => e.StartTime);
        builder.HasIndex(e => new { e.UserId, e.StartTime });
        builder.HasIndex(e => new { e.UserId, e.Project });
        builder.HasIndex(e => new { e.UserId, e.Category });
        builder.HasIndex(e => e.TaskId);
        builder.HasIndex(e => e.GoalId);
        builder.HasIndex(e => e.IsBillable);
        builder.HasIndex(e => e.IsDeleted);

        // Global query filter
        builder.HasQueryFilter(e => !e.IsDeleted);
    }
}

public class ProductivityReportConfiguration : IEntityTypeConfiguration<ProductivityReport>
{
    public void Configure(EntityTypeBuilder<ProductivityReport> builder)
    {
        builder.ToTable("ProductivityReports");

        builder.HasKey(r => r.Id);

        builder.Property(r => r.UserId)
            .IsRequired()
            .HasMaxLength(450);

        builder.Property(r => r.Type)
            .HasConversion<string>()
            .HasMaxLength(50)
            .IsRequired();

        builder.Property(r => r.FromDate)
            .IsRequired();

        builder.Property(r => r.ToDate)
            .IsRequired();

        builder.Property(r => r.GeneratedAt)
            .IsRequired();

        builder.Property(r => r.Status)
            .HasConversion<string>()
            .HasMaxLength(50)
            .IsRequired();

        builder.Property(r => r.Title)
            .IsRequired()
            .HasMaxLength(500);

        // Configure complex type as JSON
        builder.OwnsOne(r => r.ScoreCard, scoreCardBuilder =>
        {
            scoreCardBuilder.ToJson();
            scoreCardBuilder.Property(sc => sc.OverallScore);
            scoreCardBuilder.Property(sc => sc.TaskCompletionScore);
            scoreCardBuilder.Property(sc => sc.TimeManagementScore);
            scoreCardBuilder.Property(sc => sc.FocusScore);
            scoreCardBuilder.Property(sc => sc.EnergyScore);
            scoreCardBuilder.Property(sc => sc.GoalProgressScore);
            scoreCardBuilder.Property(sc => sc.HabitConsistencyScore);
            scoreCardBuilder.Property(sc => sc.WellbeingScore);
            scoreCardBuilder.Property(sc => sc.CalculatedAt);
        });

        builder.Property(r => r.Insights)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<ProductivityInsight>>(v, (JsonSerializerOptions?)null) ?? new List<ProductivityInsight>())
            .HasColumnType("json");

        builder.Property(r => r.Metrics)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<PerformanceMetric>>(v, (JsonSerializerOptions?)null) ?? new List<PerformanceMetric>())
            .HasColumnType("json");

        builder.Property(r => r.Trends)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<ProductivityTrend>>(v, (JsonSerializerOptions?)null) ?? new List<ProductivityTrend>())
            .HasColumnType("json");

        builder.Property(r => r.Recommendations)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnType("json");

        builder.Property(r => r.Data)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnType("json");

        builder.Property(r => r.Summary)
            .HasMaxLength(5000);

        // Base entity properties
        builder.Property(r => r.CreatedAt)
            .IsRequired();

        builder.Property(r => r.UpdatedAt)
            .IsRequired();

        builder.Property(r => r.IsDeleted)
            .HasDefaultValue(false);

        builder.Property(r => r.DeletedAt);

        // Indexes
        builder.HasIndex(r => r.UserId);
        builder.HasIndex(r => r.Type);
        builder.HasIndex(r => r.GeneratedAt);
        builder.HasIndex(r => new { r.UserId, r.Type });
        builder.HasIndex(r => new { r.UserId, r.FromDate, r.ToDate });
        builder.HasIndex(r => r.IsDeleted);

        // Global query filter
        builder.HasQueryFilter(r => !r.IsDeleted);
    }
}