using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class WorkflowInstanceConfiguration : IEntityTypeConfiguration<WorkflowInstance>
{
    public void Configure(EntityTypeBuilder<WorkflowInstance> builder)
    {
        builder.ToTable("WorkflowInstances");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.WorkflowTemplateId)
            .IsRequired();

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Status)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.StartedAt);

        builder.Property(x => x.CompletedAt);

        builder.Property(x => x.PausedAt);

        builder.Property(x => x.UserId)
            .HasMaxLength(100);

        builder.Property(x => x.Configuration)
            .HasColumnType("TEXT");

        builder.Property(x => x.Progress)
            .HasPrecision(5, 2)
            .HasDefaultValue(0.0m);

        builder.Property(x => x.ActualDuration);

        builder.Property(x => x.Notes)
            .HasColumnType("TEXT");

        // Configure TriggerSource as owned entity
        builder.OwnsOne(x => x.TriggerSource, ts =>
        {
            ts.Property(p => p.Type)
                .HasConversion<int>()
                .HasColumnName("TriggerSourceType");

            ts.Property(p => p.Schedule)
                .HasMaxLength(100)
                .HasColumnName("TriggerSourceSchedule");

            ts.Property(p => p.EventName)
                .HasMaxLength(100)
                .HasColumnName("TriggerSourceEventName");

            ts.Property(p => p.Condition)
                .HasMaxLength(500)
                .HasColumnName("TriggerSourceCondition");

            ts.Property(p => p.Parameters)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
                .HasColumnName("TriggerSourceParameters")
                .HasColumnType("TEXT");
        });

        // Configure Result as owned entity
        builder.OwnsOne(x => x.Result, r =>
        {
            r.Property(p => p.IsSuccess)
                .HasColumnName("ResultIsSuccess");

            r.Property(p => p.Message)
                .HasMaxLength(1000)
                .HasColumnName("ResultMessage");

            r.Property(p => p.CreatedAt)
                .HasColumnName("ResultCreatedAt");

            r.Property(p => p.Data)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
                .HasColumnName("ResultData")
                .HasColumnType("TEXT");
        });

        // Configure Context collection as JSON
        builder.Property(x => x.Context)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnName("Context")
            .HasColumnType("TEXT");

        // Base entity properties
        builder.Property(x => x.CreatedAt)
            .IsRequired();

        builder.Property(x => x.UpdatedAt)
            .IsRequired();

        builder.Property(x => x.IsDeleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.DeletedAt)
            .IsRequired(false);

        // Indexes
        builder.HasIndex(x => x.WorkflowTemplateId);
        builder.HasIndex(x => x.Status);
        builder.HasIndex(x => x.UserId);
        builder.HasIndex(x => x.StartedAt);
        builder.HasIndex(x => x.CompletedAt);
        builder.HasIndex(x => x.Progress);
        builder.HasIndex(x => x.IsDeleted);

        // Configure relationship with step instances
        builder.HasMany(x => x.StepInstances)
            .WithOne()
            .HasForeignKey("WorkflowInstanceId")
            .OnDelete(DeleteBehavior.Cascade);

        // Foreign key relationship with WorkflowTemplate
        builder.HasOne<WorkflowTemplate>()
            .WithMany()
            .HasForeignKey(x => x.WorkflowTemplateId)
            .OnDelete(DeleteBehavior.Restrict);
    }
}

public class WorkflowStepInstanceConfiguration : IEntityTypeConfiguration<WorkflowStepInstance>
{
    public void Configure(EntityTypeBuilder<WorkflowStepInstance> builder)
    {
        builder.ToTable("WorkflowStepInstances");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.WorkflowInstanceId)
            .IsRequired();

        builder.Property(x => x.StepTemplateId)
            .IsRequired();

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.Type)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.Order)
            .IsRequired();

        builder.Property(x => x.IsRequired)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.Status)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.StartedAt);

        builder.Property(x => x.CompletedAt);

        builder.Property(x => x.PausedAt);

        builder.Property(x => x.ActualDuration);

        builder.Property(x => x.Configuration)
            .HasColumnType("TEXT");

        builder.Property(x => x.Result)
            .HasColumnType("TEXT");

        builder.Property(x => x.Error)
            .HasColumnType("TEXT");

        builder.Property(x => x.Notes)
            .HasColumnType("TEXT");

        // Configure StepContext collection as JSON
        builder.Property(x => x.StepContext)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnName("StepContext")
            .HasColumnType("TEXT");

        // Base entity properties
        builder.Property(x => x.CreatedAt)
            .IsRequired();

        builder.Property(x => x.UpdatedAt)
            .IsRequired();

        builder.Property(x => x.IsDeleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.DeletedAt)
            .IsRequired(false);

        // Indexes
        builder.HasIndex(x => x.WorkflowInstanceId);
        builder.HasIndex(x => x.StepTemplateId);
        builder.HasIndex(x => x.Status);
        builder.HasIndex(x => x.Type);
        builder.HasIndex(x => x.Order);
        builder.HasIndex(x => x.IsRequired);
        builder.HasIndex(x => x.StartedAt);
        builder.HasIndex(x => x.CompletedAt);
        builder.HasIndex(x => x.IsDeleted);

        // Foreign key relationships
        builder.HasOne<WorkflowStepTemplate>()
            .WithMany()
            .HasForeignKey(x => x.StepTemplateId)
            .OnDelete(DeleteBehavior.Restrict);
    }
}