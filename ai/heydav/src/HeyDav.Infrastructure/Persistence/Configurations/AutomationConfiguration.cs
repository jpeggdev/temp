using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class AutomationRuleConfiguration : IEntityTypeConfiguration<AutomationRule>
{
    public void Configure(EntityTypeBuilder<AutomationRule> builder)
    {
        builder.ToTable("AutomationRules");

        builder.HasKey(r => r.Id);

        builder.Property(r => r.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(r => r.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(r => r.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(r => r.CreatedBy)
            .HasMaxLength(100);

        builder.Property(r => r.Category)
            .HasMaxLength(100);

        builder.Property(r => r.LastExecutionResult)
            .HasMaxLength(2000);

        builder.Property(r => r.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());

        builder.Property(r => r.Triggers)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<AutomationTrigger>>(v, (JsonSerializerOptions?)null) ?? new List<AutomationTrigger>());

        builder.Property(r => r.Conditions)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<AutomationCondition>>(v, (JsonSerializerOptions?)null) ?? new List<AutomationCondition>());

        builder.Property(r => r.Actions)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<AutomationAction>>(v, (JsonSerializerOptions?)null) ?? new List<AutomationAction>());

        // Value object conversions
        builder.OwnsOne(r => r.Schedule, schedule =>
        {
            schedule.Property(s => s.Type).HasConversion<string>();
            schedule.Property(s => s.TimeZoneId).HasMaxLength(100);
            schedule.Property(s => s.CronExpression).HasMaxLength(100);
            schedule.Property(s => s.DaysOfWeek)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(d => d.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(Enum.Parse<DayOfWeek>).ToList() ?? new List<DayOfWeek>());
            schedule.Property(s => s.DaysOfMonth)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<int>>(v, (JsonSerializerOptions?)null) ?? new List<int>());
            schedule.Property(s => s.MonthsOfYear)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<int>>(v, (JsonSerializerOptions?)null) ?? new List<int>());
            schedule.Property(s => s.CustomConfiguration)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());
        });

        builder.OwnsOne(r => r.Configuration, config =>
        {
            config.Property(c => c.Priority).HasConversion<string>();
            config.Property(c => c.RunMode).HasConversion<string>();
            config.Property(c => c.LogLevel).HasConversion<string>();
            config.Property(c => c.NotificationRecipient).HasMaxLength(200);
            config.Property(c => c.CustomSettings)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());
            config.Property(c => c.RequiredPermissions)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());
            config.Property(c => c.EnvironmentVariables)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, string>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, string>());
        });

        builder.OwnsOne(r => r.Metrics, metrics =>
        {
            metrics.Property(m => m.ErrorCounts)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, int>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, int>());
            metrics.Property(m => m.RecentExecutions)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(d => d.ToString("O")).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(DateTime.Parse).ToList() ?? new List<DateTime>());
        });

        // Collections
        builder.HasMany(r => r.Executions)
            .WithOne()
            .HasForeignKey("AutomationRuleId")
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(r => r.Name);
        builder.HasIndex(r => r.Category);
        builder.HasIndex(r => r.Status);
        builder.HasIndex(r => r.IsEnabled);
        builder.HasIndex(r => r.CreatedBy);
        builder.HasIndex(r => r.NextExecutionAt);

        // Soft delete filter
        builder.HasQueryFilter(r => !r.IsDeleted);
    }
}

public class AutomationExecutionConfiguration : IEntityTypeConfiguration<AutomationExecution>
{
    public void Configure(EntityTypeBuilder<AutomationExecution> builder)
    {
        builder.ToTable("AutomationExecutions");

        builder.HasKey(e => e.Id);

        builder.Property(e => e.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(e => e.Result)
            .HasMaxLength(2000);

        builder.Property(e => e.ErrorMessage)
            .HasMaxLength(2000);

        builder.Property(e => e.TriggeredBy)
            .HasMaxLength(100);

        builder.Property(e => e.TriggerData)
            .HasMaxLength(5000);

        builder.Property(e => e.Context)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());

        builder.Property(e => e.Output)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());

        // Collections
        builder.HasMany(e => e.ActionResults)
            .WithOne()
            .HasForeignKey("AutomationExecutionId")
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(e => e.AutomationRuleId);
        builder.HasIndex(e => e.StartedAt);
        builder.HasIndex(e => e.Status);
        builder.HasIndex(e => e.Success);
        builder.HasIndex(e => e.TriggeredBy);

        // Soft delete filter
        builder.HasQueryFilter(e => !e.IsDeleted);
    }
}

public class AutomationActionResultConfiguration : IEntityTypeConfiguration<AutomationActionResult>
{
    public void Configure(EntityTypeBuilder<AutomationActionResult> builder)
    {
        builder.ToTable("AutomationActionResults");

        builder.HasKey(r => r.Id);

        builder.Property(r => r.ActionName)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(r => r.Result)
            .HasMaxLength(2000);

        builder.Property(r => r.ErrorMessage)
            .HasMaxLength(2000);

        builder.Property(r => r.Output)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());

        // Indexes
        builder.HasIndex(r => r.ActionId);
        builder.HasIndex(r => r.StartedAt);
        builder.HasIndex(r => r.Success);

        // Soft delete filter
        builder.HasQueryFilter(r => !r.IsDeleted);
    }
}