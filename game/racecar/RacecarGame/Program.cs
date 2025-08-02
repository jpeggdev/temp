using System.Numerics;
using Raylib_cs;

namespace RacecarGame;

class Program
{
    const int ScreenWidth = 1280;
    const int ScreenHeight = 720;
    const int GuiPanelWidth = 300;
    const float CarLength = 30f;
    const float CarWidth = 15f;
    
    static void Main()
    {
        Raylib.InitWindow(ScreenWidth, ScreenHeight, "Overhead Racecar Game");
        Raylib.SetTargetFPS(60);
        
        var racecar = new RacecarPhysics(new Vector2(ScreenWidth / 2, ScreenHeight / 2));
        var camera = new Camera2D
        {
            Target = racecar.Position,
            Offset = new Vector2((ScreenWidth - GuiPanelWidth) / 2, ScreenHeight / 2),
            Rotation = 0,
            Zoom = 1f
        };
        
        bool showGui = true;
        var guiPanel = new GuiPanel(racecar);
        
        var trackBounds = new Rectangle(GuiPanelWidth + 50, 50, ScreenWidth - GuiPanelWidth - 100, ScreenHeight - 100);
        
        while (!Raylib.WindowShouldClose())
        {
            float deltaTime = Raylib.GetFrameTime();
            
            HandleInput(racecar);
            
            if (Raylib.IsKeyPressed(KeyboardKey.Tab))
                showGui = !showGui;
            
            racecar.Update(deltaTime);
            
            CheckTrackBounds(racecar, trackBounds);
            
            camera.Target = Vector2.Lerp(camera.Target, racecar.Position, 5f * deltaTime);
            
            Raylib.BeginDrawing();
            Raylib.ClearBackground(new Color(50, 50, 50, 255));
            
            Raylib.BeginMode2D(camera);
            DrawTrack(trackBounds);
            DrawRacecar(racecar);
            DrawSkidMarks(racecar);
            Raylib.EndMode2D();
            
            DrawHUD(racecar);
            
            if (showGui)
                guiPanel.Draw();
            
            Raylib.DrawText("TAB - Toggle GUI | WASD/Arrows - Drive | Space - Brake", 
                GuiPanelWidth + 10, ScreenHeight - 25, 20, Color.White);
            
            Raylib.EndDrawing();
        }
        
        Raylib.CloseWindow();
    }
    
    static void HandleInput(RacecarPhysics racecar)
    {
        float throttle = 0;
        float steering = 0;
        bool brake = false;
        
        if (Raylib.IsKeyDown(KeyboardKey.W) || Raylib.IsKeyDown(KeyboardKey.Up))
            throttle = 1f;
        else if (Raylib.IsKeyDown(KeyboardKey.S) || Raylib.IsKeyDown(KeyboardKey.Down))
            throttle = -1f;
        
        if (Raylib.IsKeyDown(KeyboardKey.A) || Raylib.IsKeyDown(KeyboardKey.Left))
            steering = -1f;
        else if (Raylib.IsKeyDown(KeyboardKey.D) || Raylib.IsKeyDown(KeyboardKey.Right))
            steering = 1f;
        
        brake = Raylib.IsKeyDown(KeyboardKey.Space);
        
        racecar.SetInput(throttle, steering, brake);
    }
    
    static void DrawTrack(Rectangle bounds)
    {
        Raylib.DrawRectangleLinesEx(bounds, 5, Color.White);
        
        for (int i = 0; i < 20; i++)
        {
            float x = bounds.X + (bounds.Width / 20) * i;
            float y = bounds.Y + bounds.Height / 2;
            if (i % 2 == 0)
                Raylib.DrawRectangle((int)x, (int)y - 2, (int)(bounds.Width / 20) - 5, 4, Color.White);
        }
    }
    
    static void DrawRacecar(RacecarPhysics racecar)
    {
        Raylib.DrawRectanglePro(
            new Rectangle(racecar.Position.X, racecar.Position.Y, CarLength, CarWidth),
            new Vector2(CarLength / 2, CarWidth / 2),
            racecar.Rotation * 180f / MathF.PI,
            Color.Red
        );
        
        Vector2 forward = new Vector2(
            MathF.Sin(racecar.Rotation) * CarLength / 2,
            -MathF.Cos(racecar.Rotation) * CarLength / 2
        );
        Vector2 frontPos = racecar.Position + forward;
        Raylib.DrawCircleV(frontPos, 5, Color.White);
    }
    
    static void DrawSkidMarks(RacecarPhysics racecar)
    {
        
    }
    
    static void DrawHUD(RacecarPhysics racecar)
    {
        int hudX = GuiPanelWidth + 10;
        int hudY = 10;
        
        Raylib.DrawText($"Speed: {racecar.GetSpeedKmh():F1} km/h", hudX, hudY, 24, Color.White);
        Raylib.DrawText($"Position: ({racecar.Position.X:F0}, {racecar.Position.Y:F0})", hudX, hudY + 30, 20, Color.White);
    }
    
    static void CheckTrackBounds(RacecarPhysics racecar, Rectangle bounds)
    {
        if (racecar.Position.X < bounds.X + CarLength / 2)
        {
            racecar.Position = new Vector2(bounds.X + CarLength / 2, racecar.Position.Y);
            racecar.Velocity = new Vector2(-racecar.Velocity.X * 0.5f, racecar.Velocity.Y);
        }
        else if (racecar.Position.X > bounds.X + bounds.Width - CarLength / 2)
        {
            racecar.Position = new Vector2(bounds.X + bounds.Width - CarLength / 2, racecar.Position.Y);
            racecar.Velocity = new Vector2(-racecar.Velocity.X * 0.5f, racecar.Velocity.Y);
        }
        
        if (racecar.Position.Y < bounds.Y + CarLength / 2)
        {
            racecar.Position = new Vector2(racecar.Position.X, bounds.Y + CarLength / 2);
            racecar.Velocity = new Vector2(racecar.Velocity.X, -racecar.Velocity.Y * 0.5f);
        }
        else if (racecar.Position.Y > bounds.Y + bounds.Height - CarLength / 2)
        {
            racecar.Position = new Vector2(racecar.Position.X, bounds.Y + bounds.Height - CarLength / 2);
            racecar.Velocity = new Vector2(racecar.Velocity.X, -racecar.Velocity.Y * 0.5f);
        }
    }
}