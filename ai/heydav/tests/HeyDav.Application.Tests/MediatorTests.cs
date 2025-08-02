using Microsoft.Extensions.DependencyInjection;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Interfaces;
using HeyDav.Domain.TodoManagement.Enums;
using Moq;
using Xunit;

namespace HeyDav.Application.Tests;

public class MediatorTests
{
    [Fact]
    public async Task Mediator_Should_Handle_Command_Successfully()
    {
        // Arrange
        var services = new ServiceCollection();
        var mockRepository = new Mock<ITodoRepository>();
        var expectedTodoId = Guid.NewGuid();
        
        mockRepository.Setup(r => r.AddAsync(It.IsAny<TodoItem>(), It.IsAny<CancellationToken>()))
            .Callback<TodoItem, CancellationToken>((todo, ct) => 
            {
                // Use reflection to set the Id
                var idProperty = typeof(TodoItem).GetProperty("Id");
                idProperty?.SetValue(todo, expectedTodoId);
            })
            .ReturnsAsync((TodoItem todo, CancellationToken ct) => todo);

        services.AddScoped<ITodoRepository>(_ => mockRepository.Object);
        services.AddApplication();
        
        var serviceProvider = services.BuildServiceProvider();
        var mediator = serviceProvider.GetRequiredService<IMediator>();

        // Act
        var command = new CreateTodoCommand("Test Todo", "Test Description", Priority.High);
        var result = await mediator.Send(command);

        // Assert
        Assert.Equal(expectedTodoId, result);
        mockRepository.Verify(r => r.AddAsync(It.IsAny<TodoItem>(), It.IsAny<CancellationToken>()), Times.Once);
    }

    [Fact]
    public async Task Mediator_Should_Handle_Query_Successfully()
    {
        // Arrange
        var services = new ServiceCollection();
        var mockRepository = new Mock<ITodoRepository>();
        var expectedTodos = new List<TodoItem> 
        { 
            TodoItem.Create("Todo 1", Priority.High),
            TodoItem.Create("Todo 2", Priority.Medium)
        };
        
        mockRepository.Setup(r => r.GetIncompleteTasksAsync(It.IsAny<CancellationToken>()))
            .ReturnsAsync(expectedTodos);

        services.AddScoped<ITodoRepository>(_ => mockRepository.Object);
        services.AddApplication();
        
        var serviceProvider = services.BuildServiceProvider();
        var mediator = serviceProvider.GetRequiredService<IMediator>();

        // Act
        var query = new GetTodosQuery();
        var result = await mediator.Send(query);

        // Assert
        Assert.Equal(2, result.Count);
        mockRepository.Verify(r => r.GetIncompleteTasksAsync(It.IsAny<CancellationToken>()), Times.Once);
    }

    [Fact]
    public async Task Mediator_Should_Throw_When_Handler_Not_Found()
    {
        // Arrange
        var services = new ServiceCollection();
        services.AddApplication();
        
        var serviceProvider = services.BuildServiceProvider();
        var mediator = serviceProvider.GetRequiredService<IMediator>();

        // Act & Assert
        var query = new UnregisteredQuery();
        await Assert.ThrowsAsync<InvalidOperationException>(() => mediator.Send(query));
    }

    // Test query without a handler
    private record UnregisteredQuery() : IQuery<string>;
}