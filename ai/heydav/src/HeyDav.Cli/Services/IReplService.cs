namespace HeyDav.Cli.Services;

public interface IReplService
{
    Task StartAsync(bool verbose = false);
    Task ExecuteCommandAsync(string command);
}