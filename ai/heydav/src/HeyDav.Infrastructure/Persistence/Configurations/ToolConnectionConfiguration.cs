using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class ToolConnectionConfiguration : IEntityTypeConfiguration<ToolConnection>
{
    public void Configure(EntityTypeBuilder<ToolConnection> builder)
    {
        builder.ToTable("ToolConnections");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .IsRequired()
            .ValueGeneratedOnAdd();

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.ToolType)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.AuthMethod)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.BaseUrl)
            .IsRequired()
            .HasMaxLength(500);

        builder.Property(x => x.ApiVersion)
            .IsRequired()
            .HasMaxLength(50);

        builder.Property(x => x.LastHealthCheck)
            .IsRequired(false);

        builder.Property(x => x.LastSuccessfulConnection)
            .IsRequired(false);

        builder.Property(x => x.LastErrorMessage)
            .IsRequired(false)
            .HasMaxLength(2000);

        builder.Property(x => x.ConsecutiveFailures)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.IsEnabled)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.RequestsPerMinute)
            .IsRequired()
            .HasDefaultValue(60);

        builder.Property(x => x.RequestsPerHour)
            .IsRequired()
            .HasDefaultValue(3600);

        builder.Property(x => x.RequestsPerDay)
            .IsRequired()
            .HasDefaultValue(86400);

        // Configuration as JSON
        builder.Property(x => x.Configuration)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<ToolConfiguration>(v, (JsonSerializerOptions?)null) ?? new ToolConfiguration()
            )
            .HasColumnType("TEXT");

        // Credentials as JSON (encrypted)
        builder.Property(x => x.Credentials)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<EncryptedCredentials>(v, (JsonSerializerOptions?)null) ?? new EncryptedCredentials()
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
        builder.HasMany(x => x.Capabilities)
            .WithOne(x => x.ToolConnection)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        builder.HasMany(x => x.WebhookEndpoints)
            .WithOne(x => x.ToolConnection)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        builder.HasMany(x => x.SyncConfigurations)
            .WithOne(x => x.ToolConnection)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.Name)
            .IsUnique();

        builder.HasIndex(x => x.ToolType);

        builder.HasIndex(x => x.Status);

        builder.HasIndex(x => x.IsEnabled);

        builder.HasIndex(x => x.LastHealthCheck);

        builder.HasIndex(x => new { x.IsDeleted, x.IsEnabled });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}