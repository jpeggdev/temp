import QtQuick
import QtQuick.Controls
import QtQuick.Window
import org.kde.plasma.core as PlasmaCore
import org.kde.plasma.plasmoid

WallpaperItem {
    id: root
    
    anchors.fill: parent
    
    property real eyeballX: width / 2
    property real eyeballY: height / 2
    property real eyeballRadius: Math.min(width, height) * 0.3
    property real irisRadius: eyeballRadius * 0.5
    property real pupilRadius: irisRadius * 0.5
    property real maxIrisOffset: eyeballRadius - irisRadius
    
    Rectangle {
        anchors.fill: parent
        color: wallpaper.configuration.backgroundColor || "#1e1e1e"
        
        MouseArea {
            id: mouseArea
            anchors.fill: parent
            hoverEnabled: true
            
            onPositionChanged: {
                updateEyePosition(mouse.x, mouse.y)
            }
        }
        
        Item {
            id: eyeball
            x: root.eyeballX - root.eyeballRadius
            y: root.eyeballY - root.eyeballRadius
            width: root.eyeballRadius * 2
            height: root.eyeballRadius * 2
            
            // Eyeball white part
            Rectangle {
                id: eyeWhite
                anchors.fill: parent
                radius: width / 2
                color: "white"
                border.color: "#cccccc"
                border.width: 2
                
                // Iris
                Rectangle {
                    id: iris
                    width: root.irisRadius * 2
                    height: root.irisRadius * 2
                    radius: width / 2
                    color: wallpaper.configuration.irisColor || "#4287f5"
                    border.color: Qt.darker(color, 1.5)
                    border.width: 2
                    
                    property real offsetX: 0
                    property real offsetY: 0
                    
                    x: parent.width / 2 - width / 2 + offsetX
                    y: parent.height / 2 - height / 2 + offsetY
                    
                    // Pupil
                    Rectangle {
                        id: pupil
                        width: root.pupilRadius * 2
                        height: root.pupilRadius * 2
                        radius: width / 2
                        color: "black"
                        anchors.centerIn: parent
                        
                        // Light reflection
                        Rectangle {
                            width: parent.width * 0.3
                            height: parent.height * 0.3
                            radius: width / 2
                            color: "white"
                            opacity: 0.7
                            x: parent.width * 0.2
                            y: parent.height * 0.2
                        }
                    }
                }
            }
            
            // Eyelid effect
            Rectangle {
                id: upperEyelid
                anchors.top: parent.top
                anchors.left: parent.left
                anchors.right: parent.right
                height: parent.height * 0.15
                color: wallpaper.configuration.backgroundColor || "#1e1e1e"
                visible: wallpaper.configuration.showEyelids !== false
                
                radius: parent.width / 2
            }
            
            Rectangle {
                id: lowerEyelid
                anchors.bottom: parent.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                height: parent.height * 0.1
                color: wallpaper.configuration.backgroundColor || "#1e1e1e"
                visible: wallpaper.configuration.showEyelids !== false
                
                radius: parent.width / 2
            }
        }
    }
    
    function updateEyePosition(mouseX, mouseY) {
        // Calculate angle and distance from eyeball center to mouse
        var dx = mouseX - root.eyeballX
        var dy = mouseY - root.eyeballY
        var distance = Math.sqrt(dx * dx + dy * dy)
        var angle = Math.atan2(dy, dx)
        
        // Limit iris movement to stay within eyeball
        var maxOffset = root.maxIrisOffset
        var actualOffset = Math.min(distance * 0.1, maxOffset)
        
        // Calculate iris offset
        iris.offsetX = Math.cos(angle) * actualOffset
        iris.offsetY = Math.sin(angle) * actualOffset
    }
    
    // Blinking animation
    Timer {
        interval: 5000 + Math.random() * 3000
        repeat: true
        running: wallpaper.configuration.enableBlinking !== false
        onTriggered: {
            blinkAnimation.start()
            interval = 5000 + Math.random() * 3000
        }
    }
    
    SequentialAnimation {
        id: blinkAnimation
        
        PropertyAnimation {
            target: upperEyelid
            property: "height"
            to: eyeball.height * 0.5
            duration: 100
            easing.type: Easing.InOutQuad
        }
        
        PropertyAnimation {
            target: lowerEyelid
            property: "height"
            to: eyeball.height * 0.5
            duration: 100
            easing.type: Easing.InOutQuad
        }
        
        PauseAnimation {
            duration: 50
        }
        
        PropertyAnimation {
            target: upperEyelid
            property: "height"
            to: eyeball.height * 0.15
            duration: 100
            easing.type: Easing.InOutQuad
        }
        
        PropertyAnimation {
            target: lowerEyelid
            property: "height"
            to: eyeball.height * 0.1
            duration: 100
            easing.type: Easing.InOutQuad
        }
    }
    
    // Try to track global mouse position
    property var rootWindow: Window.window
    
    Connections {
        target: rootWindow
        enabled: rootWindow !== null
        
        function onActiveFocusItemChanged() {
            // Update eye when window focus changes
            if (mouseArea.containsMouse) {
                updateEyePosition(mouseArea.mouseX, mouseArea.mouseY)
            }
        }
    }
    
    // Fallback: Poll for mouse position using a timer
    Timer {
        id: mouseTracker
        interval: 16 // ~60 FPS
        repeat: true
        running: true
        
        property point lastMousePos: Qt.point(root.width / 2, root.height / 2)
        
        onTriggered: {
            // Use last known position from MouseArea
            updateEyePosition(lastMousePos.x, lastMousePos.y)
        }
    }
    
    // Update last known mouse position when mouse is over the wallpaper
    Connections {
        target: mouseArea
        function onPositionChanged(mouse) {
            mouseTracker.lastMousePos = Qt.point(mouse.x, mouse.y)
            updateEyePosition(mouse.x, mouse.y)
        }
    }
}