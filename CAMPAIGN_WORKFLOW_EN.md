# Campaign Module Workflow & Business Logic

This document explains the workflow, business rules, and technical integration for the **Campaign** module in the Fundraiser application.

## 1. Lifecycle Status & Transitions

A campaign has two types of status: `status` (public) and `verified_status` (internal admin).

| Verified Status | Campaign Status | Description |
|-----------------|-----------------|-------------|
| `pending`       | `pending`       | Initial status when a campaign is newly created by a user. Not visible on the main public page. |
| `approved`      | `active`        | Admin approves the campaign. It appears publicly and is ready to receive donations. |
| `rejected`      | `suspended`     | Admin rejects the campaign. It remains hidden from the public. |

---

## 2. Creation Workflow (Create)

1.  **Input Validation**:
    *   `goal_amount` must be at least 10,000.
    *   `deadline` must be in the future (minimum tomorrow).
    *   `cover_image` is mandatory.
2.  **File Processing (R2)**:
    *   `cover_image` is stored in Cloudflare R2 under `campaigns/covers/` with a **UUID** filename.
    *   `images` (gallery) are batch-processed into `campaigns/gallery/`.
3.  **Database Transaction**:
    *   Campaign data is saved.
    *   **Tags** relationships are synchronized (Many-to-Many).
    *   Gallery data is saved to the `campaign_images` table.
    *   If any step fails, all R2 files and DB records are rolled back.

---

## 3. Production Business Rules

To maintain financial data integrity and donor trust, the following rules are strictly enforced in `CampaignService`:

### A. Funding Change Protection (Update)
*   **Rule**: Users are prohibited from changing `goal_amount` to be lower than `collected_amount` (funds already gathered).
*   **Response**: `422 Unprocessable Entity`.

### B. Deletion Policy (Delete)
*   **Rule**: Campaigns that have **already received donations** (`collected_amount > 0`) **CANNOT BE DELETED**.
*   **Reason**: For financial audit purposes. Donation data must always have a reference to its target campaign.
*   **Response**: `403 Forbidden`.
*   **Recommendation**: If a campaign is problematic but has funds, Admin should use the `suspend` or `complete` feature.

---

## 4. Image Management (R2 Auto-Cleanup)

The system automatically manages storage cleanup to prevent orphan files:

1.  **Update Cover**: When a new cover is uploaded, the old cover file in Cloudflare R2 is automatically deleted.
2.  **Update Gallery**: Uses a *Replace* strategy. All old gallery photos are deleted from R2 and the database, replaced by the new set of photos.
3.  **Delete Campaign**: If a campaign is deleted (only if `collected_amount == 0`), all related files (cover & gallery) are cleared from R2.

---

## 5. Relationships

The Campaign model is the central hub and relates to several other tables:

*   **`user`**: The owner/creator of the campaign.
*   **`category`**: The primary category of the campaign.
*   **`tags`**: Descriptive tags (Many-to-Many).
*   **`images`**: Supporting gallery images.
*   **`updates`**: Periodic progress updates posted by the creator.
*   **`donations`**: All donation records linked to this campaign.
*   **`withdrawals`**: Fund payout requests by the creator.

---

## 6. Admin Verification

Admins have full control through the `/verify` endpoint:
*   `approved`: Marks the campaign as legitimate and fit for public display.
*   `rejected`: Marks the campaign as violating rules or having incomplete data.
*   The system records `verified_by` (Admin ID), `verified_at`, and changes the `status` automatically.
