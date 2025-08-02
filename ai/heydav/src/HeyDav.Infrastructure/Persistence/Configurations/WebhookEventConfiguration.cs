using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.ToolIntegrations.Entities;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class WebhookEventConfiguration : IEntityTypeConfiguration<WebhookEvent>
{
    public void Configure(EntityTypeBuilder<WebhookEvent> builder)
    {
        builder.ToTable("WebhookEvents");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .IsRequired()
            .ValueGeneratedOnAdd();

        builder.Property(x => x.WebhookEndpointId)
            .IsRequired();

        builder.Property(x => x.EventType)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(x => x.EventId)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Payload)
            .IsRequired()
            .HasColumnType("TEXT");

        builder.Property(x => x.Headers)
            .IsRequired(false)
            .HasColumnType("TEXT");

        builder.Property(x => x.Signature)
            .IsRequired(false)
            .HasMaxLength(500);

        builder.Property(x => x.ReceivedAt)
            .IsRequired();

        builder.Property(x => x.ProcessedAt)
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

        builder.Property(x => x.RetryCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.NextRetryAt)
            .IsRequired(false);

        builder.Property(x => x.ProcessingLog)
            .IsRequired(false)
            .HasColumnType("TEXT");

        builder.Property(x => x.ProcessingDuration)
            .IsRequired(false)
            .HasConversion(
                v => v.HasValue ? (long?)v.Value.TotalMilliseconds : null,
                v => v.HasValue ? TimeSpan.FromMilliseconds(v.Value) : null
            );

        builder.Property(x => x.TriggeredWorkflows)
            .IsRequired(false)
            .HasMaxLength(1000);

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
        builder.HasOne(x => x.WebhookEndpoint)
            .WithMany(x => x.Events)
            .HasForeignKey(x => x.WebhookEndpointId)
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(x => x.WebhookEndpointId);

        builder.HasIndex(x => x.EventType);

        builder.HasIndex(x => x.EventId);

        builder.HasIndex(x => x.Status);

        builder.HasIndex(x => x.ReceivedAt);

        builder.HasIndex(x => x.ProcessedAt);

        builder.HasIndex(x => x.NextRetryAt);

        builder.HasIndex(x => new { x.WebhookEndpointId, x.EventId })
            .IsUnique();

        builder.HasIndex(x => new { x.Status, x.NextRetryAt });

        builder.HasIndex(x => new { x.IsDeleted, x.Status });

        // Global query filter for soft delete
        builder.HasQueryFilter(x => !x.IsDeleted);
    }
}