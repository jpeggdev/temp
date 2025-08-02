import QtQuick
import QtQuick.Controls
import QtQuick.Layouts

ColumnLayout {
    id: root
    
    property alias cfg_backgroundColor: backgroundColorField.text
    property alias cfg_irisColor: irisColorField.text
    property alias cfg_enableBlinking: blinkingCheckBox.checked
    property alias cfg_showEyelids: eyelidsCheckBox.checked
    
    Label {
        text: i18n("Appearance")
        font.bold: true
    }
    
    GridLayout {
        columns: 2
        columnSpacing: 10
        rowSpacing: 5
        
        Label {
            text: i18n("Background Color:")
        }
        
        TextField {
            id: backgroundColorField
            text: "#1e1e1e"
            placeholderText: i18n("#RRGGBB")
            Layout.fillWidth: true
        }
        
        Label {
            text: i18n("Iris Color:")
        }
        
        TextField {
            id: irisColorField
            text: "#4287f5"
            placeholderText: i18n("#RRGGBB")
            Layout.fillWidth: true
        }
    }
    
    Item {
        height: 20
    }
    
    Label {
        text: i18n("Behavior")
        font.bold: true
    }
    
    CheckBox {
        id: blinkingCheckBox
        text: i18n("Enable blinking")
        checked: true
    }
    
    CheckBox {
        id: eyelidsCheckBox
        text: i18n("Show eyelids")
        checked: true
    }
    
    Item {
        Layout.fillHeight: true
    }
}