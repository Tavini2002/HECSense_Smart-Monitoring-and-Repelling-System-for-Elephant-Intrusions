import { useState } from "react";
import "./App.css";

export default function App() {
  const [open, setOpen] = useState(false);
  const [tab, setTab] = useState("train");

  return (
    <div className="app-wrapper">

      {/* Floating Menu Button */}
      <button className="menu-btn" onClick={() => setOpen(true)}>
        ☰
      </button>

      {/* Sidebar */}
      <div className={`sidebar ${open ? "open" : ""}`}>
        
        {/* Close button */}
        <button className="close-btn" onClick={() => setOpen(false)}>
          ✕
        </button>

        {/* Tabs */}
        <div className="tabs">
          <button
            className={tab === "train" ? "active" : ""}
            onClick={() => setTab("train")}
          >
            🚆 Train Location
          </button>

          <button
            className={tab === "notif" ? "active" : ""}
            onClick={() => setTab("notif")}
          >
            🔔 Notifications
          </button>
        </div>

        {/* Tab Content */}
        <div className="content">
          {tab === "train" && (
            <div className="box">
              <h2>Train Location</h2>
              <p>Train location details will appear here.</p>
            </div>
          )}

          {tab === "notif" && (
            <div className="box">
              <h2>Notifications</h2>
              <p>No new notifications.</p>
            </div>
          )}
        </div>

      </div>

    </div>
  );
}
