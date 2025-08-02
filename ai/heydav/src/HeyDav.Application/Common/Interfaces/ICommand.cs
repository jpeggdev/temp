namespace HeyDav.Application.Common.Interfaces;

public interface ICommand<TResponse>
{
}

public interface ICommand : ICommand<Unit>
{
}