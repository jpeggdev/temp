using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class WorkflowTemplateConfiguration : IEntityTypeConfiguration<WorkflowTemplate>
{
    public void Configure(EntityTypeBuilder<WorkflowTemplate> builder)
    {
        builder.ToTable("WorkflowTemplates");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.Category)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.Difficulty)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.EstimatedDuration)
            .IsRequired();

        builder.Property(x => x.IsActive)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.IsBuiltIn)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.CreatedBy)
            .HasMaxLength(100);

        builder.Property(x => x.Version)
            .IsRequired()
            .HasDefaultValue(1);

        builder.Property(x => x.ConfigurationSchema)
            .HasColumnType("TEXT");

        builder.Property(x => x.UsageCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.Rating)
            .HasPrecision(3, 2)
            .HasDefaultValue(0.0m);

        builder.Property(x => x.RatingCount)
            .IsRequired()
            .HasDefaultValue(0);

        // Configure AutoTrigger as owned entity
        builder.OwnsOne(x => x.AutoTrigger, at =>
        {
            at.Property(p => p.Type)
                .HasConversion<int>()
                .HasColumnName("AutoTriggerType");

            at.Property(p => p.Schedule)
                .HasMaxLength(100)
                .HasColumnName("AutoTriggerSchedule");

            at.Property(p => p.EventName)
                .HasMaxLength(100)
                .HasColumnName("AutoTriggerEventName");

            at.Property(p => p.Condition)
                .HasMaxLength(500)
                .HasColumnName("AutoTriggerCondition");

            at.Property(p => p.Parameters)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
                .HasColumnName("AutoTriggerParameters")
                .HasColumnType("TEXT");
        });

        // Configure Tags collection as JSON
        builder.Property(x => x.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("Tags")
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
        builder.HasIndex(x => x.Category);
        builder.HasIndex(x => x.Difficulty);
        builder.HasIndex(x => x.IsActive);
        builder.HasIndex(x => x.IsBuiltIn);
        builder.HasIndex(x => x.CreatedBy);
        builder.HasIndex(x => x.Rating);
        builder.HasIndex(x => x.UsageCount);
        builder.HasIndex(x => x.IsDeleted);

        // Configure relationship with step templates
        builder.HasMany(x => x.StepTemplates)
            .WithOne()
            .HasForeignKey("WorkflowTemplateId")
            .OnDelete(DeleteBehavior.Cascade);
    }
}

public class WorkflowStepTemplateConfiguration : IEntityTypeConfiguration<WorkflowStepTemplate>
{
    public void Configure(EntityTypeBuilder<WorkflowStepTemplate> builder)
    {
        builder.ToTable("WorkflowStepTemplates");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.WorkflowTemplateId)
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

        builder.Property(x => x.Configuration)
            .HasColumnType("TEXT");

        builder.Property(x => x.EstimatedDuration);

        // Configure Dependencies collection as JSON
        builder.Property(x => x.Dependencies)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("Dependencies")
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
        builder.HasIndex(x => x.Type);
        builder.HasIndex(x => x.Order);
        builder.HasIndex(x => x.IsRequired);
        builder.HasIndex(x => x.IsDeleted);
    }
}