using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.ValueObjects;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class AIAgentConfiguration : IEntityTypeConfiguration<AIAgent>
{
    public void Configure(EntityTypeBuilder<AIAgent> builder)
    {
        builder.ToTable("AIAgents");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(x => x.Description)
            .HasMaxLength(500);

        builder.Property(x => x.Type)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.Status)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.LastActiveAt)
            .IsRequired(false);

        builder.Property(x => x.LastHealthCheckAt)
            .IsRequired(false);

        builder.Property(x => x.LastError)
            .HasMaxLength(1000);

        builder.Property(x => x.SuccessfulTasksCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.FailedTasksCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.AverageResponseTime)
            .IsRequired()
            .HasDefaultValue(0.0);

        // Configure AgentConfiguration as owned entity
        builder.OwnsOne(x => x.Configuration, config =>
        {
            config.Property(p => p.ModelName)
                .IsRequired()
                .HasMaxLength(100)
                .HasColumnName("ConfigModelName");

            config.Property(p => p.MaxTokens)
                .IsRequired()
                .HasColumnName("ConfigMaxTokens");

            config.Property(p => p.Temperature)
                .IsRequired()
                .HasColumnName("ConfigTemperature");

            config.Property(p => p.MaxConcurrentTasks)
                .IsRequired()
                .HasColumnName("ConfigMaxConcurrentTasks");

            config.Property(p => p.TaskTimeout)
                .IsRequired()
                .HasColumnName("ConfigTaskTimeout");

            config.Property(p => p.CustomSettings)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, string>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, string>())
                .HasColumnName("ConfigCustomSettings");
        });

        // Configure collections as JSON
        builder.Property(x => x.Capabilities)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("Capabilities");

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
        builder.HasIndex(x => x.Name).IsUnique();
        builder.HasIndex(x => x.Type);
        builder.HasIndex(x => x.Status);
        builder.HasIndex(x => x.LastActiveAt);
        builder.HasIndex(x => x.IsDeleted);

        // Configure CurrentTasks as navigation property
        builder.HasMany(x => x.CurrentTasks)
            .WithOne()
            .HasForeignKey(t => t.AssignedAgentId)
            .OnDelete(DeleteBehavior.SetNull);
    }
}