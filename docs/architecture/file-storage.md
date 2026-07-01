# File Storage Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the file storage architecture of YachtOS.

The platform stores business-related files such as maintenance photos, receipts, documents and user avatars.

The storage solution must be secure, scalable and independent of the application server.

---

## Storage Strategy

YachtOS will use S3-compatible object storage.

The application must never depend on the local server filesystem for permanent file storage.

---

## Storage Types

The platform may store:

- User avatars
- Maintenance photos
- Expense receipts
- Invoices
- Certificates
- Documents
- Charter attachments

Each file belongs to exactly one business entity.

---

## File Ownership

Every uploaded file must be associated with:

- Tenant
- Business module
- Related record
- Uploading user

Files must never exist without a business owner.

---

## File Metadata

The system should store:

- Original file name
- Stored file name
- MIME type
- File size
- Storage path
- Uploaded by
- Upload date

Business data should reference metadata rather than the physical file.

---

## Access Control

File access must follow the same authorization rules as the related business record.

Users must not access files belonging to another tenant.

Public file URLs should not be used for protected business documents.

---

## Upload Rules

Uploads must validate:

- File type
- File size
- File extension
- Authorization
- Related business record

Invalid uploads must be rejected.

---

## Naming Strategy

Stored file names should never use the original filename.

The storage layer should generate unique identifiers to avoid collisions.

---

## Deletion Strategy

Files should only be deleted when allowed by business rules.

Historical files associated with audit data should remain available.

Physical deletion may occur after the related business record is permanently removed, if permitted.

---

## Future Compatibility

The storage architecture supports future expansion for:

- Multiple storage providers
- CDN integration
- Image optimization
- File versioning
- Virus scanning
- Temporary upload storage

---

## Non-Goals

The MVP will not include:

- Image editing
- Video transcoding
- AI image analysis
- OCR processing

These features may be introduced in future versions.

---

## File Storage Principle

Business data owns files.

Files never exist independently from the business records they support.