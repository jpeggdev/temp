using HeyDav.Application;
using HeyDav.Infrastructure;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Configuration;
using System.CommandLine;
using HeyDav.Cli.Services;

var builder = Host.CreateApplicationBuilder(args);

builder.Configuration
    .AddJsonFile("appsettings.json", optional: true)
    .AddEnvironmentVariables();

builder.Services.AddApplication();
builder.Services.AddInfrastructure(builder.Configuration);
builder.Services.AddSingleton<IReplService, ReplService>();

var host = builder.Build();

var rootCommand = new RootCommand("Hey-Dav CLI Interface");

var replCommand = new Command("repl", "Start interactive REPL mode")
{
    new Option<bool>("--verbose", "Enable verbose output")
};

replCommand.SetHandler(async (bool verbose) =>
{
    var replService = host.Services.GetRequiredService<IReplService>();
    await replService.StartAsync(verbose);
}, replCommand.Options.OfType<Option<bool>>().First());

var executeCommand = new Command("exec", "Execute a single command")
{
    new Argument<string>("command", "The command to execute")
};

executeCommand.SetHandler(async (string command) =>
{
    var replService = host.Services.GetRequiredService<IReplService>();
    await replService.ExecuteCommandAsync(command);
}, executeCommand.Arguments.OfType<Argument<string>>().First());

rootCommand.AddCommand(replCommand);
rootCommand.AddCommand(executeCommand);

return await rootCommand.InvokeAsync(args);
