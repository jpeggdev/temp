using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.ValueObjects;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class McpServerConfiguration : IEntityTypeConfiguration<McpServer>
{
    public void Configure(EntityTypeBuilder<McpServer> builder)
    {
        builder.ToTable("McpServers");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(100);

        builder.Property(x => x.Description)
            .HasMaxLength(500);

        builder.Property(x => x.IsActive)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.LastConnectedAt)
            .IsRequired(false);

        builder.Property(x => x.LastHealthCheckAt)
            .IsRequired(false);

        builder.Property(x => x.LastError)
            .HasMaxLength(1000);

        builder.Property(x => x.Version)
            .HasMaxLength(50);

        builder.Property(x => x.SuccessfulRequestsCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.FailedRequestsCount)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.AverageResponseTime)
            .IsRequired()
            .HasDefaultValue(0.0);

        // Configure McpServerEndpoint as owned entity
        builder.OwnsOne(x => x.Endpoint, endpoint =>
        {
            endpoint.Property(p => p.Name)
                .IsRequired()
                .HasMaxLength(100)
                .HasColumnName("EndpointName");

            endpoint.Property(p => p.Protocol)
                .IsRequired()
                .HasMaxLength(10)
                .HasColumnName("EndpointProtocol");

            endpoint.Property(p => p.Host)
                .IsRequired()
                .HasMaxLength(255)
                .HasColumnName("EndpointHost");

            endpoint.Property(p => p.Port)
                .IsRequired()
                .HasColumnName("EndpointPort");

            endpoint.Property(p => p.Path)
                .HasMaxLength(255)
                .HasColumnName("EndpointPath");

            endpoint.Property(p => p.RequiresAuthentication)
                .IsRequired()
                .HasColumnName("EndpointRequiresAuth");

            endpoint.Property(p => p.Headers)
                .HasConversion(
                    v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                    v => JsonSerializer.Deserialize<Dictionary<string, string>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, string>())
                .HasColumnName("EndpointHeaders");
        });

        // Configure collections as JSON
        builder.Property(x => x.SupportedTools)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("SupportedTools");

        builder.Property(x => x.Metadata)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<Dictionary<string, object>>(v, (JsonSerializerOptions?)null) ?? new Dictionary<string, object>())
            .HasColumnName("Metadata");

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
        builder.HasIndex(x => x.IsActive);
        builder.HasIndex(x => x.LastConnectedAt);
        builder.HasIndex(x => x.IsDeleted);
    }
}