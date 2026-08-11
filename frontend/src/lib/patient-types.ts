import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Patient API contracts. Field names mirror the backend Patient model exactly
 * (10-API §7); nothing is invented.
 */

export type PatientStatus = 'active' | 'inactive' | 'archived';

export interface Patient {
  id: string;
  tenant_id: string;
  master_record_id: string;
  enterprise_person_id: string;
  name: string | null;
  dob: string | null;
  sex: string | null;
  status: PatientStatus;
  version: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

export interface PaginationMeta {
  page: number;
  pageSize: number;
  total: number;
}

export type PatientListResponse = ApiEnvelope<Patient[], PaginationMeta>;
export type PatientResponse = ApiEnvelope<Patient>;

/** Fields accepted by POST /patients (CreatePatientRequest). */
export interface CreatePatientPayload {
  name: string;
  dob?: string | null;
  sex?: string | null;
  external_ref?: string | null;
}

export interface PatientIdentifier {
  id: string;
  patient_id: string;
  identity_type_id: string;
  value: string;
  status: string;
  created_at: string;
}

export interface PatientDemographic {
  id: string;
  patient_id: string;
  ethnicity: string | null;
  status: string;
  created_at: string;
}

export interface PatientConsent {
  id: string;
  patient_id: string;
  consent_type_id: string;
  status: string;
  created_at: string;
}

export interface PatientRelation {
  id: string;
  patient_id: string;
  related_patient_id: string;
  relation_type_id: string;
  status: string;
  created_at: string;
}

export interface PatientAlias {
  id: string;
  patient_id: string;
  name: string;
  status: string;
  created_at: string;
}

export type PatientChildResource = 'identifiers' | 'demographics' | 'consents' | 'relations' | 'aliases';

export const PATIENT_STATUSES: PatientStatus[] = ['active', 'inactive', 'archived'];
