using System;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace HeyDav.Infrastructure.Migrations
{
    /// <inheritdoc />
    public partial class AddAgentManagement : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "AIAgents",
                columns: table => new
                {
                    Id = table.Column<Guid>(type: "TEXT", nullable: false),
                    Name = table.Column<string>(type: "TEXT", maxLength: 100, nullable: false),
                    Description = table.Column<string>(type: "TEXT", maxLength: 500, nullable: true),
                    Type = table.Column<int>(type: "INTEGER", nullable: false),
                    Status = table.Column<int>(type: "INTEGER", nullable: false),
                    ConfigModelName = table.Column<string>(type: "TEXT", maxLength: 100, nullable: false),
                    ConfigMaxTokens = table.Column<int>(type: "INTEGER", nullable: false),
                    ConfigTemperature = table.Column<double>(type: "REAL", nullable: false),
                    ConfigMaxConcurrentTasks = table.Column<int>(type: "INTEGER", nullable: false),
                    ConfigTaskTimeout = table.Column<TimeSpan>(type: "TEXT", nullable: false),
                    ConfigCustomSettings = table.Column<string>(type: "TEXT", nullable: false),
                    LastActiveAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    LastHealthCheckAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    LastError = table.Column<string>(type: "TEXT", maxLength: 1000, nullable: true),
                    SuccessfulTasksCount = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 0),
                    FailedTasksCount = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 0),
                    AverageResponseTime = table.Column<double>(type: "REAL", nullable: false, defaultValue: 0.0),
                    Capabilities = table.Column<string>(type: "TEXT", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false, defaultValue: false),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_AIAgents", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "McpServers",
                columns: table => new
                {
                    Id = table.Column<Guid>(type: "TEXT", nullable: false),
                    Name = table.Column<string>(type: "TEXT", maxLength: 100, nullable: false),
                    Description = table.Column<string>(type: "TEXT", maxLength: 500, nullable: true),
                    EndpointName = table.Column<string>(type: "TEXT", maxLength: 100, nullable: false),
                    EndpointProtocol = table.Column<string>(type: "TEXT", maxLength: 10, nullable: false),
                    EndpointHost = table.Column<string>(type: "TEXT", maxLength: 255, nullable: false),
                    EndpointPort = table.Column<int>(type: "INTEGER", nullable: false),
                    EndpointPath = table.Column<string>(type: "TEXT", maxLength: 255, nullable: true),
                    EndpointHeaders = table.Column<string>(type: "TEXT", nullable: false),
                    EndpointRequiresAuth = table.Column<bool>(type: "INTEGER", nullable: false),
                    IsActive = table.Column<bool>(type: "INTEGER", nullable: false, defaultValue: false),
                    LastConnectedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    LastHealthCheckAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    LastError = table.Column<string>(type: "TEXT", maxLength: 1000, nullable: true),
                    Version = table.Column<string>(type: "TEXT", maxLength: 50, nullable: true),
                    SuccessfulRequestsCount = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 0),
                    FailedRequestsCount = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 0),
                    AverageResponseTime = table.Column<double>(type: "REAL", nullable: false, defaultValue: 0.0),
                    SupportedTools = table.Column<string>(type: "TEXT", nullable: false),
                    Metadata = table.Column<string>(type: "TEXT", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false, defaultValue: false),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_McpServers", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "AgentTasks",
                columns: table => new
                {
                    Id = table.Column<Guid>(type: "TEXT", nullable: false),
                    Title = table.Column<string>(type: "TEXT", maxLength: 200, nullable: false),
                    Description = table.Column<string>(type: "TEXT", maxLength: 1000, nullable: true),
                    Priority = table.Column<int>(type: "INTEGER", nullable: false),
                    Status = table.Column<int>(type: "INTEGER", nullable: false),
                    AssignedAgentId = table.Column<Guid>(type: "TEXT", nullable: true),
                    ScheduledAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    StartedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    CompletedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    DueDate = table.Column<DateTime>(type: "TEXT", nullable: true),
                    Result = table.Column<string>(type: "TEXT", maxLength: 2000, nullable: true),
                    ErrorMessage = table.Column<string>(type: "TEXT", maxLength: 1000, nullable: true),
                    RetryCount = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 0),
                    MaxRetries = table.Column<int>(type: "INTEGER", nullable: false, defaultValue: 3),
                    RequiredCapabilities = table.Column<string>(type: "TEXT", nullable: false),
                    Parameters = table.Column<string>(type: "TEXT", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false, defaultValue: false),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_AgentTasks", x => x.Id);
                    table.ForeignKey(
                        name: "FK_AgentTasks_AIAgents_AssignedAgentId",
                        column: x => x.AssignedAgentId,
                        principalTable: "AIAgents",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.SetNull);
                });

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_AssignedAgentId",
                table: "AgentTasks",
                column: "AssignedAgentId");

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_DueDate",
                table: "AgentTasks",
                column: "DueDate");

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_IsDeleted",
                table: "AgentTasks",
                column: "IsDeleted");

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_Priority",
                table: "AgentTasks",
                column: "Priority");

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_ScheduledAt",
                table: "AgentTasks",
                column: "ScheduledAt");

            migrationBuilder.CreateIndex(
                name: "IX_AgentTasks_Status",
                table: "AgentTasks",
                column: "Status");

            migrationBuilder.CreateIndex(
                name: "IX_AIAgents_IsDeleted",
                table: "AIAgents",
                column: "IsDeleted");

            migrationBuilder.CreateIndex(
                name: "IX_AIAgents_LastActiveAt",
                table: "AIAgents",
                column: "LastActiveAt");

            migrationBuilder.CreateIndex(
                name: "IX_AIAgents_Name",
                table: "AIAgents",
                column: "Name",
                unique: true);

            migrationBuilder.CreateIndex(
                name: "IX_AIAgents_Status",
                table: "AIAgents",
                column: "Status");

            migrationBuilder.CreateIndex(
                name: "IX_AIAgents_Type",
                table: "AIAgents",
                column: "Type");

            migrationBuilder.CreateIndex(
                name: "IX_McpServers_IsActive",
                table: "McpServers",
                column: "IsActive");

            migrationBuilder.CreateIndex(
                name: "IX_McpServers_IsDeleted",
                table: "McpServers",
                column: "IsDeleted");

            migrationBuilder.CreateIndex(
                name: "IX_McpServers_LastConnectedAt",
                table: "McpServers",
                column: "LastConnectedAt");

            migrationBuilder.CreateIndex(
                name: "IX_McpServers_Name",
                table: "McpServers",
                column: "Name",
                unique: true);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "AgentTasks");

            migrationBuilder.DropTable(
                name: "McpServers");

            migrationBuilder.DropTable(
                name: "AIAgents");
        }
    }
}
