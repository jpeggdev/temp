using System.Numerics;

namespace RacecarGame;

public class RacecarPhysics
{
    public Vector2 Position { get; set; }
    public Vector2 Velocity { get; set; }
    public float Rotation { get; set; }
    public float AngularVelocity { get; set; }
    
    public float MaxSpeed { get; set; } = 300f;
    public float Acceleration { get; set; } = 150f;
    public float BrakeForce { get; set; } = 200f;
    public float TurnSpeed { get; set; } = 3f;
    public float Grip { get; set; } = 0.95f;
    public float Drag { get; set; } = 0.98f;
    public float AngularDrag { get; set; } = 0.9f;
    public float SlipAngleThreshold { get; set; } = 30f;
    public float SlipGrip { get; set; } = 0.7f;
    
    private float currentThrottle;
    private float currentSteering;
    private bool isBraking;
    
    public RacecarPhysics(Vector2 startPosition)
    {
        Position = startPosition;
        Velocity = Vector2.Zero;
        Rotation = 0;
        AngularVelocity = 0;
    }
    
    public void SetInput(float throttle, float steering, bool brake)
    {
        currentThrottle = Math.Clamp(throttle, -1f, 1f);
        currentSteering = Math.Clamp(steering, -1f, 1f);
        isBraking = brake;
    }
    
    public void Update(float deltaTime)
    {
        Vector2 forward = new Vector2((float)Math.Sin(Rotation), -(float)Math.Cos(Rotation));
        Vector2 right = new Vector2(forward.Y, -forward.X);
        
        if (isBraking)
        {
            float brakeAmount = BrakeForce * deltaTime;
            float currentSpeed = Velocity.Length();
            if (currentSpeed > 0)
            {
                Vector2 brakeDirection = -Vector2.Normalize(Velocity);
                Velocity += brakeDirection * Math.Min(brakeAmount, currentSpeed);
            }
        }
        else if (Math.Abs(currentThrottle) > 0.01f)
        {
            Vector2 accelerationVector = forward * (currentThrottle * Acceleration * deltaTime);
            Velocity += accelerationVector;
        }
        
        float speed = Velocity.Length();
        if (speed > MaxSpeed)
        {
            Velocity = Vector2.Normalize(Velocity) * MaxSpeed;
        }
        
        if (speed > 5f && Math.Abs(currentSteering) > 0.01f)
        {
            float steerAmount = currentSteering * TurnSpeed * deltaTime;
            steerAmount *= Math.Min(1f, speed / 100f);
            
            AngularVelocity += steerAmount;
        }
        
        AngularVelocity *= AngularDrag;
        Rotation += AngularVelocity * deltaTime;
        
        if (speed > 0.1f)
        {
            Vector2 velocityDirection = Vector2.Normalize(Velocity);
            float slipAngle = Vector2.Dot(velocityDirection, right) * 90f;
            
            float currentGrip = Math.Abs(slipAngle) > SlipAngleThreshold ? SlipGrip : Grip;
            
            Vector2 lateralVelocity = right * Vector2.Dot(Velocity, right);
            Velocity -= lateralVelocity * (1f - (1f - currentGrip));
        }
        
        Velocity *= Drag;
        
        Position += Velocity * deltaTime;
        
        while (Rotation > MathF.PI) Rotation -= MathF.PI * 2;
        while (Rotation < -MathF.PI) Rotation += MathF.PI * 2;
    }
    
    public float GetSpeed()
    {
        return Velocity.Length();
    }
    
    public float GetSpeedKmh()
    {
        return GetSpeed() * 3.6f / 10f;
    }
}