
# Final System Correction – README

## IMPORTANT NOTICE
This document defines the **FINAL corrections and stabilization tasks** for the website system.
Only **bug fixes and functional corrections** must be implemented.

⚠️ **NO changes to the design, layout, UI structure, or page architecture are allowed.**
Only backend logic, database relations, and functional errors should be corrected.

The platform contains **three main sections that must remain structurally unchanged**:

1. Administrator
2. Enterprise
3. Student / Stagiaire

All fixes must respect the current structure of these three systems.

---

# 1. Message Counter Logic Error (Student Dashboard)

## Problem
The **Messages card in the Student Dashboard** is not synchronized with the actual discussions.

Messages sent inside:
- Support
- Message section
- View Discussion

are not correctly reflected in the **Messages counter**.

## Expected Behavior

The message counter must represent **the number of offers that have new replies**, not the number of individual messages.

Example:

If a student applied to 5 offers:
- Offer 1 → company replied → count 1
- Offer 2 → company replied → count 2
- Offer 3 → no reply → count stays 2
- Offer 4 → company replied → count 3
- Offer 5 → company replied → count 4

Even if a company sends **20–60 messages in the same discussion**, it must count as **ONE notification**.

When the student opens the discussion, the counter must **decrease automatically**.

### Required Fix

- Track unread messages **per offer**
- Not per message
- Update the counter when the discussion is opened

Database relation must connect:

- user
- offer
- discussion messages
- read status

---

# 2. CV PDF Export Error (Student Section)

## Problem

When exporting the CV from **Création de Dossier**, the PDF generates **two pages**:

Page 1 → Only name, title, and photo  
Page 2 → All remaining information

This is incorrect.

## Expected Behavior

All CV content must be exported **on a single page** when the data fits on one page.

The PDF must contain:

- photo
- title
- personal info
- experience
- skills
- education

### Required Fix

Possible causes:
- CSS page-break error
- PDF rendering issue
- Incorrect layout container size

Ensure the PDF renderer keeps the content **within one page** when possible.

---

# 3. Offer Update Error (Enterprise)

## Problem

On the **Déposer Offer page**, clicking **Modification / Update** causes an error.

The update process fails.

## Required Fix

Check:

- API route
- database update query
- form validation

The update function must properly modify the existing offer in the database.

---

# 4. Offer Deletion Error (Enterprise)

## Problem

Deleting an offer from **Déposer Offer** does not work correctly.

## Required Fix

Ensure:

- Delete route works
- Database deletion query works
- UI updates after deletion

---

# 5. Database Creation Error

There is an error preventing proper **database creation or connection**.

## Required Fix

Check:

- database schema
- migrations
- connection configuration

The system must initialize without errors.

---

# 6. Candidate List Not Displayed (Enterprise)

## Problem

The **Candidate page shows no candidates**, even when applications exist.

However, candidates appear in **Déposer Offer**, which creates inconsistency.

## Required Fix

Correct the **Gestion de Candidat** logic.

Ensure:

- candidates are fetched correctly
- offers link to applications
- database relations are correct

---

# 7. Enterprise Settings Error

## Problem

Changes in **Parameters / Settings** refresh the page and produce an error.

## Required Fix

Check:

- update request
- database write
- form validation

Settings must save successfully.

---

# 8. Administrator – Create Offer Options

The **Create Offer page** must contain the same selectable data as Enterprise.

Required options:

Localization examples:
- regions
- cities

Categories:
- Informatics
- Telecommunications
- etc.

Contract type:
- Alternance
- Stage

Status:
- Active
- Archive

Administrator must be able to create offers with these values.

---

# 9. Administrator – Manage Offers Filters

Filters do not work.

Filters include:

Status:
- Active
- Archive

Type:
- Alternance
- Stage

## Required Fix

Filtering logic must query the database correctly and update the UI.

---

# 10. Administrator – Candidates Filter

Filters on the **Candidates page** do not work.

Filtering by:

- Alternance
- Stage

must correctly display candidates.

---

# 11. Candidate Profile / CV Display Error

## Problems

1. "Voir Profil" button must be removed.
2. Replace with **"Lettre de Motivation"** button.
3. "Voir CV" must properly open the CV.
4. The **block button** must work correctly.

Blocked candidates should become unavailable.

---

# 12. My Company – Profile Image Management

Add the ability to:

- upload company image
- change company image
- delete company image

Image must be stored in database and reflected in UI.

---

# 13. Administrator MonCompte Page Corrections

Required changes:

Remove:
- settings icon
- slide bar parameter system

Add:

- company logo management
- profile data update
- language selection

Data must be synchronized with database.

The same corrections must also apply to the **Student section**.

---

# 14. FINAL SYSTEM ARCHITECTURE RULE

The platform must function as **one integrated system** between:

- Administrator
- Enterprise
- Student / Stagiaire

## Database Core Entities

- companies
- users
- offers
- candidatures
- permissions

Each user must be linked to a company using:

company_id

---

# Company Registration Logic

When a company registers:

1. Create the company record
2. Create an administrator account
3. Link admin to the company

Admin must be redirected to **Administrator Dashboard** after login.

---

# Enterprise Workspace

Each company must operate in its own isolated environment.

Employees only see data belonging to **their company**.

Cross-company data access must be impossible.

---

# Administrator Role

Administrator must be able to:

- manage company profile
- create employees
- manage employees
- assign permissions
- manage offers
- manage candidatures

Administrator must **never automatically become an employee**.

---

# Employee Role

Employees belong to the same company.

Access rights depend on **permissions assigned by the administrator**.

Permissions may include:

- create offer
- modify offer
- delete offer
- manage candidatures

---

# Student / Stagiaire Section

Students must be able to:

- browse offers
- apply to offers
- manage their applications

Students must not access enterprise or administrator areas.

---

# FINAL SYSTEM REQUIREMENT

The system must be:

- stable
- error-free
- fully connected to the database
- consistent between all sections

Errors that must be eliminated:

- database errors
- backend logic errors
- routing errors
- permission conflicts

---

# IMPORTANT FINAL PROMPT

Use this README as the **final correction specification**.

Implement all fixes strictly according to these rules:

1. Do NOT modify the design.
2. Do NOT modify the page layout.
3. Do NOT modify UI structure.
4. Only correct functionality, backend logic, and database relations.

After corrections, the system must behave as a **single synchronized platform** between:

Administrator, Enterprise, and Student systems.
