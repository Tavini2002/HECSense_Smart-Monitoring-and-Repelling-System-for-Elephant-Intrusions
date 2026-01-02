**Overall Project Repository**
**Overview**

This repository contains the development work for our project. 
At the current stage, the **complete integration is not available in a single branch.** 
However, **all features are implemented and working correctly in individual branches.**



Each team member has developed their assigned modules in their respective branches.



**Branch Structure \& Contributors**


| Branch Name | Contributor | IT Number | Description |
|-------------|-------------|-----------|-------------|
| IT22904300Sivashangar | Sivashangar S. | IT22904300 | Elephant Detection and Risk Classification System |
| Tavini | Perera K.A.T.M. | IT22917102 | Smart Train Control System for Collision Prevention |
| Keerthana | Keerthana S. | IT22182128 | Emotion, Health and Behavior – Based Elephant Monitoring & Alert System |
| Harshika | Harshika M. | IT22064554 | Smart Eco–Friendly Crop Shielding & Adaptive Repelling System |




All functionalities are available branch-wise and can be reviewed individually.

**Integration Status**

&nbsp;	Full integration into a single branch is not completed

&nbsp;	All features are successfully implemented branch-wise

&nbsp;	No broken functionality within individual branches





**Repository URL**

https://github.com/Tavini2002/HECSense\_Smart-Monitoring-and-Repelling-System-for-Elephant-Intrusions.git


**Project System Diagram**

![HECSENSE System Diagram](<System Diagram - HECSense.png>)

**How to run each component**

Keerthana
---------------
        Step 01: Start XAMMP
        Step 02: php artisan serve (Start web app)
        Step 03: activate ai-env 
        Step 04: python main-app.py

Harshika
---------------

        AI App
        --------------
        Step: 01: Start XAMMP
        Step: 02: activate ai-env
        Step: 03: streamlit run main-app.py

        Web App
        --------------

        Step:01 php -S localhost:8000


Tavini
---------------
        Step 01: Open VS Code
        Step 02: Navigate to backend --> server
        Step 03: Open VS Code Terminal and run "npm run dev"
        Step 04: Navigate to backend --> api-gateway
        Step 05: Open VS Code Terminal and run "npm run dev"
        Step 06: Navigate to frontend
        Step 05: Open VS Code Terminal and run "npm run dev"


Sivashangar
---------------
        Step 01: Open VS Code
        Step 02: Navigate to sensor_server
        Step 03: Open VS Code Terminal and run "npm server"
        Step 04: Connect ESP32 sensor to a power source

        If server is already running;
                the ESP32 will automatically connect to it.

        If the ESP32 does not connect to the server;
                1. Check the IPv4 address of the device where the server is running.
                2. Update the IP address in the ESP32 code with the correct IPv4 address.
                3. Upload the updated code to the ESP32.
                4. Power up the ESP32 again.

        Both the ESP32 and the server device must be connected to the same network.



# Train-Project
[Train Project Architecture Diagram](https://drive.google.com/file/d/1zul0nLJYjdgD0Dux_9ji6uScgKo8Gw-I/view?usp=drivesdk)