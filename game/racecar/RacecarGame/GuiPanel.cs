using System.Numerics;
using Raylib_cs;

namespace RacecarGame;

public class GuiPanel
{
    private readonly RacecarPhysics racecar;
    private const int PanelWidth = 300;
    private const int SliderHeight = 30;
    private const int LabelWidth = 120;
    private const int ValueWidth = 60;
    
    public GuiPanel(RacecarPhysics racecar)
    {
        this.racecar = racecar;
    }
    
    public void Draw()
    {
        Raylib.DrawRectangle(0, 0, PanelWidth, Raylib.GetScreenHeight(), new Color(30, 30, 30, 240));
        Raylib.DrawLine(PanelWidth, 0, PanelWidth, Raylib.GetScreenHeight(), Color.White);
        
        int y = 10;
        Raylib.DrawText("RACECAR SETTINGS", 10, y, 24, Color.White);
        y += 40;
        
        racecar.MaxSpeed = DrawSlider(ref y, "Max Speed", racecar.MaxSpeed, 50f, 500f, "km/h");
        racecar.Acceleration = DrawSlider(ref y, "Acceleration", racecar.Acceleration, 50f, 300f, "m/s²");
        racecar.BrakeForce = DrawSlider(ref y, "Brake Force", racecar.BrakeForce, 50f, 400f, "m/s²");
        racecar.TurnSpeed = DrawSlider(ref y, "Turn Speed", racecar.TurnSpeed, 1f, 5f, "rad/s");
        racecar.Grip = DrawSlider(ref y, "Grip", racecar.Grip, 0.5f, 1f, "");
        racecar.Drag = DrawSlider(ref y, "Drag", racecar.Drag, 0.9f, 0.99f, "");
        racecar.AngularDrag = DrawSlider(ref y, "Angular Drag", racecar.AngularDrag, 0.8f, 0.95f, "");
        racecar.SlipAngleThreshold = DrawSlider(ref y, "Slip Angle", racecar.SlipAngleThreshold, 10f, 60f, "°");
        racecar.SlipGrip = DrawSlider(ref y, "Slip Grip", racecar.SlipGrip, 0.3f, 0.9f, "");
        
        y += 20;
        if (DrawButton(10, y, PanelWidth - 20, 40, "RESET POSITION"))
        {
            racecar.Position = new Vector2(Raylib.GetScreenWidth() / 2, Raylib.GetScreenHeight() / 2);
            racecar.Velocity = Vector2.Zero;
            racecar.Rotation = 0;
            racecar.AngularVelocity = 0;
        }
        
        y += 50;
        if (DrawButton(10, y, PanelWidth - 20, 40, "RESET TO DEFAULTS"))
        {
            racecar.MaxSpeed = 300f;
            racecar.Acceleration = 150f;
            racecar.BrakeForce = 200f;
            racecar.TurnSpeed = 3f;
            racecar.Grip = 0.95f;
            racecar.Drag = 0.98f;
            racecar.AngularDrag = 0.9f;
            racecar.SlipAngleThreshold = 30f;
            racecar.SlipGrip = 0.7f;
        }
    }
    
    private float DrawSlider(ref int y, string label, float value, float min, float max, string unit)
    {
        int x = 10;
        
        Raylib.DrawText(label, x, y, 16, Color.White);
        
        string valueText = $"{value:F2}{(unit.Length > 0 ? " " + unit : "")}";
        int textWidth = Raylib.MeasureText(valueText, 14);
        Raylib.DrawText(valueText, PanelWidth - textWidth - 10, y, 14, Color.Gray);
        
        y += 20;
        
        float sliderWidth = PanelWidth - 20;
        float sliderX = x;
        
        Raylib.DrawRectangle((int)sliderX, y, (int)sliderWidth, 6, new Color(60, 60, 60, 255));
        
        float normalizedValue = (value - min) / (max - min);
        float handleX = sliderX + normalizedValue * sliderWidth;
        
        bool isHovered = CheckCollisionPointRec(Raylib.GetMousePosition(), 
            new Rectangle(handleX - 8, y - 4, 16, 14));
        
        if (isHovered && Raylib.IsMouseButtonDown(MouseButton.Left))
        {
            float mouseX = Raylib.GetMousePosition().X;
            normalizedValue = Math.Clamp((mouseX - sliderX) / sliderWidth, 0f, 1f);
            value = min + normalizedValue * (max - min);
        }
        
        Raylib.DrawCircle((int)handleX, y + 3, 8, isHovered ? Color.White : Color.LightGray);
        
        y += 20;
        return value;
    }
    
    private bool DrawButton(int x, int y, int width, int height, string text)
    {
        Rectangle rect = new Rectangle(x, y, width, height);
        bool isHovered = CheckCollisionPointRec(Raylib.GetMousePosition(), rect);
        bool isClicked = isHovered && Raylib.IsMouseButtonPressed(MouseButton.Left);
        
        Color buttonColor = isHovered ? new Color(80, 80, 80, 255) : new Color(60, 60, 60, 255);
        Raylib.DrawRectangleRec(rect, buttonColor);
        Raylib.DrawRectangleLinesEx(rect, 2, Color.White);
        
        int textWidth = Raylib.MeasureText(text, 18);
        int textX = x + (width - textWidth) / 2;
        int textY = y + (height - 18) / 2;
        Raylib.DrawText(text, textX, textY, 18, Color.White);
        
        return isClicked;
    }
    
    private bool CheckCollisionPointRec(Vector2 point, Rectangle rect)
    {
        return point.X >= rect.X && point.X <= rect.X + rect.Width &&
               point.Y >= rect.Y && point.Y <= rect.Y + rect.Height;
    }
}