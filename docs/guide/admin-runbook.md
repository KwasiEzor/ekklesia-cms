# Admin Runbook — Ekklesia CMS

This guide is designed for church treasurers, pastors, and administrators to help you manage your Ekklesia CMS platform effectively.

---

## 🏛️ Church Management

### 1. Members
*   **Creating a Member:** Navigate to the **Membres** section. Click **Nouveau membre**. Fill in personal details, phone number, and baptism date if applicable.
*   **Cell Groups:** Assign members to cell groups to help organize home meetings.
*   **Exporting Data:** Use the **Export** button on the list page to download a CSV/Excel file of your members for church reports.

### 2. Giving & Finances
*   **Recording a Donation:** Go to **Dons**. Click **Nouveau don**. You can link it to a member or leave it anonymous.
*   **Immutability Rule:** Once a donation is saved, it **cannot be edited or deleted** to maintain financial integrity.
*   **Corrections:** If you make a mistake, use the **Void** (Annuler) action on the list row. You will be asked for a reason. This will not delete the record but will mark it as voided with a clear audit trail. Then, create a new record with the correct information.
*   **Reports:** Export giving records to CSV for monthly financial reports.

---

## 📢 Content Management

### 1. Sermons (Prédications)
*   Upload or link audio and video recordings.
*   The system can automatically generate a slug from your title for clean URLs.

### 2. Events & Announcements
*   **Events:** Set start/end dates, location, and registration capacity.
*   **Announcements:** Use these for news that expires. You can "pin" important announcements to the top of your member portal.

### 3. Page Builder
*   Manage static pages (e.g., "About Us") using the block-based editor.

---

## 🤖 AI Assistant

### 1. Using the Assistant
*   Click the **Assistant IA** in the sidebar.
*   You can ask the AI to help write announcements, summarize sermon notes, or give you trends on church giving.
*   **Pro-tip:** The AI respects privacy and will never reveal individual donor names or phone numbers—it only sees "Safe Totals."

### 2. AI Skills
*   You can explicitly invoke skills by typing `/` followed by the skill slug:
    *   `/sermon-outline` — Create a plan for your next message.
    *   `/giving-insights` — Analyze monthly trends.
    *   `/translate` — Translate content between French and English.
*   **Automatic Detection:** The AI will often sense your intent even without the `/` command.

---

## ⚙️ Settings

### 1. Church Identity
*   Update your church name, pastor, and contact details under **Paramètres**.
*   Upload your logo and favicon for the member portal.

### 2. Notification Channels
*   Toggle which notifications (Email/SMS) are enabled for different types of events.

---

## 🛠️ Troubleshooting

*   **Financial Discrepancies:** Always check the **Audit Log** (Journal d'activité) to see who made what changes.
*   **System Health:** Super Admins can monitor the overall system status from the dashboard monitoring tools.
*   **Support:** Contact your system administrator for technical issues related to your AI API keys or SMS provider.
