using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class ToolSyncConfigurationConfiguration : IEntityTypeConfiguration<ToolSyncConfiguration>
{
    public void Configure(EntityTypeBuilder<ToolSyncConfiguration> builder)
    {
        builder.ToTable("ToolSyncConfigurations");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .IsRequired()
            .ValueGeneratedOnAdd();

        builder.Property(x => x.ToolConnectionId)
            .IsRequired();

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.Direction)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.Frequency)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.IsEnabled)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.EntityType)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(x => x.RemoteEntityType)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(x => x.ConflictResolution)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.NextSyncAt)
            .IsRequired(false);

        builder.Property(x => x.LastSyncAt)
            .IsRequired(false);

        builder.Property(x => x.LastSuccessfulSyncAt)
            .IsRequired(false);

        builder.Property(x => x.LastSyncError)
            .IsRequired(false)
            .HasMaxLength(2000);

        builder.Property(x => x.ConsecutiveFailures)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.TotalSyncAttempts)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.SuccessfulSyncs)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsCreated)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsUpdated)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsDeleted)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.ConflictsResolved)
            .IsRequired()
            .HasDefaultValue(0);

        // Settings as JSON
        builder.Property(x => x.Settings)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<SyncSettings>(v, (JsonSerializerOptions?)null) ?? new SyncSettings()
            )
            .HasColumnType("TEXT");

        // Field mappings as JSON
        builder.Property(x => x.FieldMappings)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<FieldMappingConfiguration>(v, (JsonSerializerOptions?)null) ?? new FieldMappingConfiguration()
            )
            .HasColumnType("TEXT");

        // Base entity properties
        builder.Property(x => x.CreatedAt)
            .IsRequired()
            .HasDefaultValueSql("datetime('now')");

        builder.Property(x => x.UpdatedAt)
            .IsRequired()
            .HasDefaultValueSql("datetime('now')");

        builder.Property(x => x.IsDeleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.DeletedAt)
            .IsRequired(false);

        // Relationships
        builder.HasOne(x => x.ToolConnection)
            .WithMany(x => x.SyncConfigurations)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        builder.HasMany(x => x.ExecutionLogs)
            .WithOne(x => x.SyncConfiguration)
            .HasForeignKey(x => x.SyncConfigurationId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.ToolConnectionId);

        builder.HasIndex(x => x.Name)
            .IsUnique();

        builder.HasIndex(x => x.EntityType);

        builder.HasIndex(x => x.IsEnabled);

        builder.HasIndex(x => x.NextSyncAt);

        builder.HasIndex(x => x.LastSyncAt);

        builder.HasIndex(x => new { x.IsDeleted, x.IsEnabled });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}

public class SyncExecutionLogConfiguration : IEntityTypeConfiguration<SyncExecutionLog>
{
    public void Configure(EntityTypeBuilder<SyncExecutionLog> builder)
    {
        builder.ToTable("SyncExecutionLogs");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .IsRequired()
            .ValueGeneratedOnAdd();

        builder.Property(x => x.SyncConfigurationId)
            .IsRequired();

        builder.Property(x => x.StartedAt)
            .IsRequired();

        builder.Property(x => x.CompletedAt)
            .IsRequired(false);

        builder.Property(x => x.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.WasSuccessful)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.ErrorMessage)
            .IsRequired(false)
            .HasMaxLength(2000);

        builder.Property(x => x.ErrorDetails)
            .IsRequired(false)
            .HasColumnType("TEXT");

        builder.Property(x => x.RecordsProcessed)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsCreated)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsUpdated)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsDeleted)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.RecordsSkipped)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.ConflictsDetected)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.ConflictsResolved)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.ExecutionDuration)
            .IsRequired(false)
            .HasConversion(
                v => v.HasValue ? (long?)v.Value.TotalMilliseconds : null,
                v => v.HasValue ? TimeSpan.FromMilliseconds(v.Value) : null
            );

        builder.Property(x => x.RecordsPerSecond)
            .IsRequired(false);

        builder.Property(x => x.MemoryUsedBytes)
            .IsRequired(false);

        builder.Property(x => x.ExecutionLog)
            .IsRequired(false)
            .HasColumnType("TEXT");

        builder.Property(x => x.WarningMessages)
            .IsRequired(false)
            .HasColumnType("TEXT");

        // Base entity properties
        builder.Property(x => x.CreatedAt)
            .IsRequired()
            .HasDefaultValueSql("datetime('now')");

        builder.Property(x => x.UpdatedAt)
            .IsRequired()
            .HasDefaultValueSql("datetime('now')");

        builder.Property(x => x.IsDeleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.DeletedAt)
            .IsRequired(false);

        // Relationships
        builder.HasOne(x => x.SyncConfiguration)
            .WithMany(x => x.ExecutionLogs)
            .HasForeignKey(x => x.SyncConfigurationId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.SyncConfigurationId);

        builder.HasIndex(x => x.StartedAt);

        builder.HasIndex(x => x.Status);

        builder.HasIndex(x => x.WasSuccessful);

        builder.HasIndex(x => new { x.SyncConfigurationId, x.StartedAt });

        builder.HasIndex(x => new { x.IsDeleted, x.Status });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}