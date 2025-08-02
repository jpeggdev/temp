using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class WebhookEndpointConfiguration : IEntityTypeConfiguration<WebhookEndpoint>
{
    public void Configure(EntityTypeBuilder<WebhookEndpoint> builder)
    {
        builder.ToTable("WebhookEndpoints");

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

        builder.Property(x => x.EndpointUrl)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.Secret)
            .IsRequired()
            .HasMaxLength(500);

        builder.Property(x => x.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(x => x.ContentType)
            .IsRequired()
            .HasMaxLength(100)
            .HasDefaultValue("application/json");

        builder.Property(x => x.TimeoutSeconds)
            .IsRequired()
            .HasDefaultValue(30);

        builder.Property(x => x.MaxRetries)
            .IsRequired()
            .HasDefaultValue(3);

        builder.Property(x => x.IsEnabled)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.TotalEventsReceived)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.SuccessfulProcessing)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.FailedProcessing)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.LastEventReceived)
            .IsRequired(false);

        builder.Property(x => x.LastSuccessfulEvent)
            .IsRequired(false);

        builder.Property(x => x.LastErrorMessage)
            .IsRequired(false)
            .HasMaxLength(2000);

        // Supported events as JSON array
        builder.Property(x => x.SupportedEvents)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>()
            )
            .HasColumnType("TEXT");

        // Enabled events as JSON array
        builder.Property(x => x.EnabledEvents)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>()
            )
            .HasColumnType("TEXT");

        // Security settings as JSON
        builder.Property(x => x.SecuritySettings)
            .IsRequired()
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<WebhookSecuritySettings>(v, (JsonSerializerOptions?)null) ?? new WebhookSecuritySettings()
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
            .WithMany(x => x.WebhookEndpoints)
            .HasForeignKey(x => x.ToolConnectionId)
            .OnDelete(DeleteBehavior.Cascade);

        builder.HasMany(x => x.Events)
            .WithOne(x => x.WebhookEndpoint)
            .HasForeignKey(x => x.WebhookEndpointId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.ToolConnectionId);

        builder.HasIndex(x => x.EndpointUrl)
            .IsUnique();

        builder.HasIndex(x => x.Status);

        builder.HasIndex(x => x.IsEnabled);

        builder.HasIndex(x => x.LastEventReceived);

        builder.HasIndex(x => new { x.IsDeleted, x.IsEnabled });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}