1. Core Objectives
1.	Centralized SHS student database using the KD–SHS ID standard.
2.	Multi-tenant for schools – each school has its own data space, but under one national schema.
3.	Secure APIs for:
o	KampusDiscount app/platform
o	Partner banks (Access, etc.)
o	Future partners (GES, WAEC, EduTech apps)
4.	Flexible onboarding – bulk uploads, manual entry, and self-registration with approval.
5.	Annual rollover of student classes (Form 1 → 2 → 3 → completed) with minimal manual work.
 
2. High-Level Architecture
•	Core Database Layer
o	Relational DB (PostgreSQL or MySQL) holding:
	Schools
	Students
	Guardians
	Verification records
	Users & roles
	Audit logs
•	API Layer
o	REST (and optionally GraphQL later)
o	Exposed via:
	Public Partner API (limited, read-only, API keys / OAuth2)
	Internal KampusDiscount API (read/write)
	School Admin API (for portals)
•	Admin & School Portal
o	Web app where:
	KampusDiscount ops team can manage all schools & approvals
	School admins can upload/update students
	Ambassadors can verify students
•	Background Jobs & Services
o	ID generator service
o	Bulk import processor (CSV/Excel)
o	Yearly promotion/completion cron jobs
o	Data quality & deduplication scripts
 
3. Data Model (Key Entities)

3.1 School
•	id
•	school_code (e.g. PRESCO)
•	name
•	type (Public, Mission, Private, Technical, Vocational)
•	region
•	district
•	address
•	contact_phone
•	contact_email
•	status (Active, Inactive)
•	created_at, updated_at

3.2 Student
•	id
•	kd_shs_id (KD–SHS–SCHOOL_CODE–YEAR–SERIAL)
•	first_name
•	last_name
•	other_names
•	gender
•	date_of_birth
•	photo_url
•	school_id (FK → School)
•	form (1, 2, 3)
•	programme (Science, Business, Arts, etc.)
•	house (optional)
•	index_number (optional – BECE/WASSCE/school index)
•	year_of_admission
•	completion_year
•	admission_status (Active, Transfer, Completed, Dropped)
•	created_at, updated_at

3.3 Guardian
•	id
•	student_id (FK → Student)
•	name
•	relationship
•	phone
•	address (optional)
•	email (optional)

3.4 Verification
•	id
•	student_id (FK)
•	method (SchoolUpload, SchoolAdminApproval, AmbassadorVerification, SelfRegistration)
•	verified_by_user_id (FK → User)
•	status (Pending, Approved, Rejected)
•	remarks
•	verified_at

3.5 User (Portal Users)
•	id
•	full_name
•	email
•	phone
•	role (KD_Admin, School_Admin, Ambassador, ReadOnly_Partner)
•	school_id (nullable – only for school users)
•	password_hash (if local auth)
•	status
•	created_at, updated_at

3.6 Audit Log
•	id
•	entity_type (Student, School, Verification, etc.)
•	entity_id
•	action (CREATE, UPDATE, DELETE, VIEW)
•	performed_by_user_id
•	ip_address
•	timestamp
•	details (JSON)
 
4. Functional Requirements

4.1 School Onboarding & Management
•	Create, update, deactivate schools.
•	Assign school codes (unique).
•	Create school admin accounts with proper roles.
•	View student totals per school, region, year, etc.

4.2 Student Data Ingestion

Bulk Upload (primary)
•	Upload Excel/CSV with the template you defined.
•	Map columns to fields (pre-mapped if template followed).
•	Validate:
o	Required fields
o	Data types
o	Duplicates (by kd_shs_id, name + dob + school, index number)
•	Show upload preview with errors/warnings.
•	On approval, bulk insert into DB.

Manual Entry (per student)
•	Form to manually add one student.
•	ID auto-generated using ID service.

Self-Registration (optional future feature)
•	Student submits own data via app or web form.
•	Flows into a school-specific “Pending” queue.
•	School Admin / Ambassador approves or rejects.

4.3 KD–SHS ID Generation Service
•	ID generation microservice or module.
•	Pattern: KD–SHS–{school_code}–{year_of_admission}–{serial}.
•	Serial is unique per school per year (e.g. zero-padded 5 digits).
•	Must guarantee no collisions under high concurrency.

4.4 Verification Workflow
•	For each student:
o	Maintain Verification record(s).
o	Rules:
	Only “Approved” students can be exposed via external APIs for eligibility checks.
o	Allow:
	School admins to approve uploaded records as “Verified”.
	Ambassadors to verify using mobile app/portal.
	KD admins to override in special cases.

4.5 Search & Retrieval (Internal)
•	Search students by:
o	KD_SHS_ID
o	Name + School
o	Form & Programme
o	Year of admission/completion
•	Filter by:
o	School
o	Region
o	Admission status
o	Verification status

4.6 Annual Rollover / Promotions
•	End-of-year process:
o	Promote Form 1 → 2 and Form 2 → 3.
o	Mark Form 3 as “Completed”.
•	Allow:
o	Manual adjustments for repeats/transfers.
o	Logs of all changes.

4.7 Reporting & Analytics
•	Number of students:
o	Per school / region / year / form / programme.
•	Verification coverage:
o	Verified vs Pending vs Rejected.
•	Growth over time.
•	Export reports as CSV.

4.8 API Access Control
•	Partner APIs:
o	For banks, fintechs, GES, etc.
o	Use API keys + IP allow-list or OAuth2.
o	Rate limiting per partner.
•	KampusDiscount Internal APIs:
o	Used by your app/platform.
o	JWT/OAuth2 with service-level permissions.
•	School APIs:
o	For schools that want system-to-system sync later.
o	Limited scope to their own data only.
 
5. API Design (Retrieval via APIs)

Assume REST, JSON, HTTPS.

5.1 Authentication & Authorization
•	/auth/token
o	Input: client_id, client_secret (for machine-to-machine) OR username/password (for portal users).
o	Output: access_token (JWT) with role & school context.
•	API keys for some partners:
o	Header: X-API-Key: <key>

5.2 Student APIs

GET 
/api/students/{kd_shs_id}
•	Use case: Check if a given student exists & is verified.
•	Response sample:
{
  "kd_shs_id": "KD-SHS-PRESCO-2024-00348",
  "first_name": "Kwame",
  "last_name": "Mensah",
  "gender": "M",
  "date_of_birth": "2008-05-12",
  "school": {
    "school_code": "PRESCO",
    "name": "Presbyterian Boys' Secondary School",
    "region": "Greater Accra"
  },
  "form": 2,
  "programme": "Science",
  "year_of_admission": 2023,
  "completion_year": 2026,
  "admission_status": "Active",
  "verification_status": "Approved"
}

GET 
/api/students
•	Use case: Search/filter.
•	Query params:
o	school_code
o	form
o	programme
o	region
o	admission_status
o	verification_status
o	year_of_admission
o	completion_year
o	page, page_size
•	Response: paginated list.

POST 
/api/students/bulk
•	Restricted (School_Admin or KD_Admin).
•	Accepts:
o	file (CSV/Excel upload) OR
o	JSON array of student objects.
•	Initiates a bulk import job; returns job_id.
•	Job status tracked via /api/imports/{job_id}.

5.3 Verification APIs

GET 
/api/students/{kd_shs_id}/verification
•	Return verification history & current status.

POST 
/api/students/{kd_shs_id}/verification
•	For admins/ambassadors to update status.
•	Body:
{
  "status": "Approved",
  "remarks": "Verified by school admin from upload list"
}

5.4 Eligibility APIs for Partners (Banks, etc.)

POST 
/api/eligibility/student
•	Use case: Partner checks if this Ghanaian is a genuine SHS student and active.
•	Input:
{
  "kd_shs_id": "KD-SHS-PRESCO-2024-00348"
}

•	OR, for lower trust:
{
  "first_name": "Kwame",
  "last_name": "Mensah",
  "school_code": "PRESCO",
  "date_of_birth": "2008-05-12"
}

•	Output:
{
  "is_valid_student": true,
  "match_confidence": 0.97,
  "verification_status": "Approved",
  "school": {
    "school_code": "PRESCO",
    "name": "Presbyterian Boys' Secondary School"
  },
  "form": 2,
  "programme": "Science"
}

You can design different “views” of student data for different partners (e.g. bank sees less PII than KampusDiscount itself).
 
6. Non-Functional Requirements

6.1 Security & Privacy
•	All traffic over HTTPS/TLS.
•	Encryption at rest for DB or at least for sensitive columns (phone, DOB).
•	Strict role-based access control:
o	School admins only see their own students.
o	Partners only see the minimum data they need.
•	Audit logs for:
o	Data views (who viewed student X).
o	Updates & deletions.
•	Compliance mindset with data protection laws (Ghana Data Protection Act).

6.2 Performance & Scalability
•	Designed to handle:
o	1–2 million student records.
o	High read traffic for eligibility checks.
•	Use:
o	DB indexing on:
	kd_shs_id
	(school_id, form, admission_status)
	(last_name, first_name, school_id)
o	Caching layer (Redis) for frequent eligibility lookups.
•	Rate limiting per API key / client.

6.3 Reliability & Backups
•	Daily automated backups; point-in-time recovery if possible.
•	Staging vs production environments.
•	Monitoring (APM, logs, alerts).
•	Disaster recovery plan.
 

