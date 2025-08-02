using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class NotificationConfiguration : IEntityTypeConfiguration<Notification>
{
    public void Configure(EntityTypeBuilder<Notification> builder)
    {
        builder.ToTable("Notifications");

        builder.HasKey(n => n.Id);

        builder.Property(n => n.Title)
            .IsRequired()
            .HasMaxLength(500);

        builder.Property(n => n.Content)
            .IsRequired()
            .HasMaxLength(5000);

        builder.Property(n => n.Type)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(n => n.Priority)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(n => n.Status)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(n => n.Channel)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(n => n.RecipientId)
            .HasMaxLength(100);

        builder.Property(n => n.RecipientEmail)
            .HasMaxLength(500);

        builder.Property(n => n.RecipientPhone)
            .HasMaxLength(50);

        builder.Property(n => n.RelatedEntityType)
            .HasMaxLength(100);

        builder.Property(n => n.ErrorMessage)
            .HasMaxLength(2000);

        // Value object conversions
        builder.OwnsOne(n => n.Metadata, metadata =>
        {
            metadata.Property(m => m.ImageUrl).HasMaxLength(1000);
            metadata.Property(m => m.IconUrl).HasMaxLength(1000);
            metadata.Property(m => m.Sound).HasMaxLength(100);
            metadata.Property(m => m.Color).HasMaxLength(20);
            metadata.Property(m => m.DeepLink).HasMaxLength(1000);
            metadata.Property(m => m.Category).HasMaxLength(100);
            metadata.Property(m => m.CustomData)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());
            metadata.Property(m => m.Tags)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());
        });

        builder.OwnsOne(n => n.Actions, actions =>
        {
            actions.Property(a => a.ReplyPlaceholder).HasMaxLength(200);
            actions.Property(a => a.Actions)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<NotificationAction>>(v, (JsonSerializerOptions?)null) ?? new List<NotificationAction>());
            actions.Property(a => a.SnoozeOptions)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(s => s.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(TimeSpan.Parse).ToList() ?? new List<TimeSpan>());
        });

        // Collections
        builder.HasMany(n => n.DeliveryAttempts)
            .WithOne()
            .HasForeignKey("NotificationId")
            .OnDelete(DeleteBehavior.Cascade);

        builder.HasMany(n => n.Interactions)
            .WithOne()
            .HasForeignKey("NotificationId")
            .OnDelete(DeleteBehavior.Cascade);

        // Indexes
        builder.HasIndex(n => n.RecipientId);
        builder.HasIndex(n => n.Type);
        builder.HasIndex(n => n.Status);
        builder.HasIndex(n => n.Priority);
        builder.HasIndex(n => n.ScheduledAt);
        builder.HasIndex(n => n.CreatedAt);
        builder.HasIndex(n => new { n.RelatedEntityType, n.RelatedEntityId });

        // Soft delete filter
        builder.HasQueryFilter(n => !n.IsDeleted);
    }
}

public class NotificationDeliveryAttemptConfiguration : IEntityTypeConfiguration<NotificationDeliveryAttempt>
{
    public void Configure(EntityTypeBuilder<NotificationDeliveryAttempt> builder)
    {
        builder.ToTable("NotificationDeliveryAttempts");

        builder.HasKey(a => a.Id);

        builder.Property(a => a.Channel)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(a => a.ErrorMessage)
            .HasMaxLength(2000);

        builder.Property(a => a.ResponseData)
            .HasMaxLength(5000);

        // Indexes
        builder.HasIndex(a => a.NotificationId);
        builder.HasIndex(a => a.AttemptedAt);

        // Soft delete filter
        builder.HasQueryFilter(a => !a.IsDeleted);
    }
}

public class NotificationInteractionConfiguration : IEntityTypeConfiguration<NotificationInteraction>
{
    public void Configure(EntityTypeBuilder<NotificationInteraction> builder)
    {
        builder.ToTable("NotificationInteractions");

        builder.HasKey(i => i.Id);

        builder.Property(i => i.InteractionType)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(i => i.Data)
            .HasMaxLength(2000);

        builder.Property(i => i.UserAgent)
            .HasMaxLength(500);

        builder.Property(i => i.IpAddress)
            .HasMaxLength(50);

        // Indexes
        builder.HasIndex(i => i.NotificationId);
        builder.HasIndex(i => i.InteractedAt);
        builder.HasIndex(i => i.InteractionType);

        // Soft delete filter
        builder.HasQueryFilter(i => !i.IsDeleted);
    }
}

public class NotificationPreferenceConfiguration : IEntityTypeConfiguration<NotificationPreference>
{
    public void Configure(EntityTypeBuilder<NotificationPreference> builder)
    {
        builder.ToTable("NotificationPreferences");

        builder.HasKey(p => p.Id);

        builder.Property(p => p.UserId)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(p => p.NotificationType)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(p => p.PreferredChannel)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(p => p.MinimumPriority)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(p => p.CustomSettings)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>());

        // Value object conversions
        builder.OwnsOne(p => p.SchedulingPreference, sp =>
        {
            sp.Property(s => s.GroupingStrategy).HasConversion<string>();
            sp.Property(s => s.BatchingStrategy).HasConversion<string>();
            sp.Property(s => s.PreferredTimes)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(t => t.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(TimeOnly.Parse).ToList() ?? new List<TimeOnly>());
            sp.Property(s => s.WorkingDays)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(d => d.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(Enum.Parse<DayOfWeek>).ToList() ?? new List<DayOfWeek>());
        });

        builder.OwnsOne(p => p.DoNotDisturbSettings, dnd =>
        {
            dnd.Property(d => d.Mode).HasConversion<string>();
            dnd.Property(d => d.AllowedPriorities)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(p => p.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(Enum.Parse<Domain.Notifications.Enums.NotificationPriority>).ToList() ?? new List<Domain.Notifications.Enums.NotificationPriority>());
            dnd.Property(d => d.AllowedTypes)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(t => t.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(Enum.Parse<Domain.Notifications.Enums.NotificationType>).ToList() ?? new List<Domain.Notifications.Enums.NotificationType>());
            dnd.Property(d => d.Days)
                .HasConversion(
                    v => JsonSerializer.Serialize(v.Select(d => d.ToString()).ToList(), (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null)?.Select(Enum.Parse<DayOfWeek>).ToList() ?? new List<DayOfWeek>());
        });

        // Indexes
        builder.HasIndex(p => p.UserId);
        builder.HasIndex(p => new { p.UserId, p.NotificationType }).IsUnique();

        // Soft delete filter
        builder.HasQueryFilter(p => !p.IsDeleted);
    }
}

public class NotificationTemplateConfiguration : IEntityTypeConfiguration<NotificationTemplate>
{
    public void Configure(EntityTypeBuilder<NotificationTemplate> builder)
    {
        builder.ToTable("NotificationTemplates");

        builder.HasKey(t => t.Id);

        builder.Property(t => t.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(t => t.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(t => t.Type)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(t => t.TitleTemplate)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(t => t.ContentTemplate)
            .IsRequired()
            .HasMaxLength(10000);

        builder.Property(t => t.DefaultPriority)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(t => t.DefaultChannel)
            .IsRequired()
            .HasConversion<string>();

        builder.Property(t => t.Category)
            .HasMaxLength(100);

        builder.Property(t => t.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());

        builder.Property(t => t.Variables)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<NotificationTemplateVariable>>(v, (JsonSerializerOptions?)null) ?? new List<NotificationTemplateVariable>());

        // Value object conversions
        builder.OwnsOne(t => t.Metadata, metadata =>
        {
            metadata.Property(m => m.Version).HasMaxLength(20);
            metadata.Property(m => m.Author).HasMaxLength(100);
            metadata.Property(m => m.SupportedChannels)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());
            metadata.Property(m => m.Dependencies)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>());
            metadata.Property(m => m.Localizations)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, string>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, string>());
        });

        builder.OwnsOne(t => t.DefaultActions, actions =>
        {
            actions.Property(a => a.Actions)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<List<NotificationAction>>(v, (JsonSerializerOptions?)null) ?? new List<NotificationAction>());
        });

        // Indexes
        builder.HasIndex(t => t.Name).IsUnique();
        builder.HasIndex(t => t.Type);
        builder.HasIndex(t => t.Category);
        builder.HasIndex(t => t.IsActive);

        // Soft delete filter
        builder.HasQueryFilter(t => !t.IsDeleted);
    }
}