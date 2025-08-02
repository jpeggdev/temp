using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class ToolCapabilityConfiguration : IEntityTypeConfiguration<ToolCapability>
{
    public void Configure(EntityTypeBuilder<ToolCapability> builder)
    {
        builder.ToTable("ToolCapabilities");

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

        builder.Property(x => x.Type)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.IsEnabled)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.RequiresAuthentication)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.RequiredScopes)
            .IsRequired(false)
            .HasMaxLength(500);

        builder.Property(x => x.MaxRequestsPerMinute)
            .IsRequired(false);

        builder.Property(x => x.MaxRequestsPerHour)
            .IsRequired(false);

        builder.Property(x => x.TotalUsageCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.LastUsed)
            .IsRequired(false);

        builder.Property(x => x.SuccessfulOperations)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.FailedOperations)
            .IsRequired()
            .HasDefaultValue(0);

        // Configuration as JSON
        builder.Property(x => x.Configuration)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<CapabilityConfiguration>(v, (JsonSerializerOptions?)null) ?? new CapabilityConfiguration()
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
            .WithMany(x => x.Capabilities)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.ToolConnectionId);

        builder.HasIndex(x => x.Name);

        builder.HasIndex(x => x.Type);

        builder.HasIndex(x => x.IsEnabled);

        builder.HasIndex(x => x.LastUsed);

        builder.HasIndex(x => new { x.ToolConnectionId, x.Name })
            .IsUnique();

        builder.HasIndex(x => new { x.IsDeleted, x.IsEnabled });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}