import { Route, Routes } from 'react-router-dom';
import { AppShell } from '@/components/layout/app-shell';
import { DashboardPage } from '@/pages/dashboard-page';
import { PatientsPage } from '@/pages/patients-page';
import { PatientNewPage } from '@/pages/patient-new-page';
import { PatientDetailPage } from '@/pages/patient-detail-page';
import { StaffPage } from '@/pages/staff-page';
import { StaffNewPage } from '@/pages/staff-new-page';
import { StaffDetailPage } from '@/pages/staff-detail-page';
import { ProvidersPage } from '@/pages/providers-page';
import { ProviderNewPage } from '@/pages/provider-new-page';
import { ProviderDetailPage } from '@/pages/provider-detail-page';
import { OrganizationsPage } from '@/pages/organizations-page';
import { OrganizationNewPage } from '@/pages/organization-new-page';
import { OrganizationDetailPage } from '@/pages/organization-detail-page';
import { EnterprisePersonsPage } from '@/pages/enterprise-persons-page';
import { EnterprisePersonDetailPage } from '@/pages/enterprise-person-detail-page';
import { ReferenceDataPage } from '@/pages/reference-data-page';
import { ReferenceCategoryNewPage } from '@/pages/reference-category-new-page';
import { ReferenceCategoryDetailPage } from '@/pages/reference-category-detail-page';
import { ReferenceValuesPage } from '@/pages/reference-values-page';
import { ReferenceValueNewPage } from '@/pages/reference-value-new-page';
import { ReferenceValueDetailPage } from '@/pages/reference-value-detail-page';
import { SearchPage } from '@/pages/search-page';
import { MasterRecordsPage } from '@/pages/master-records-page';
import { MasterRecordDetailPage } from '@/pages/master-record-detail-page';
import { MasterRecordVersionDetailPage } from '@/pages/master-record-version-detail-page';
import { DuplicatesPage } from '@/pages/duplicates-page';
import { GoldenRecordsPage } from '@/pages/golden-records-page';
import { MergesPage } from '@/pages/merges-page';
import { ApprovalsPage } from '@/pages/approvals-page';
import { StewardshipPage } from '@/pages/stewardship-page';
import { ImportsPage } from '@/pages/imports-page';
import { ExportsPage } from '@/pages/exports-page';
import { IntegrationsPage } from '@/pages/integrations-page';
import { AuditPage } from '@/pages/audit-page';
import { NotFoundPage } from '@/pages/not-found-page';

export default function App() {
  return (
    <Routes>
      <Route element={<AppShell />}>
        <Route index element={<DashboardPage />} />
        <Route path="patients">
          <Route index element={<PatientsPage />} />
          <Route path="new" element={<PatientNewPage />} />
          <Route path=":id" element={<PatientDetailPage />} />
        </Route>
        <Route path="staff">
          <Route index element={<StaffPage />} />
          <Route path="new" element={<StaffNewPage />} />
          <Route path=":id" element={<StaffDetailPage />} />
        </Route>
        <Route path="providers">
          <Route index element={<ProvidersPage />} />
          <Route path="new" element={<ProviderNewPage />} />
          <Route path=":id" element={<ProviderDetailPage />} />
        </Route>
        <Route path="organizations">
          <Route index element={<OrganizationsPage />} />
          <Route path="new" element={<OrganizationNewPage />} />
          <Route path=":id" element={<OrganizationDetailPage />} />
        </Route>
        <Route path="enterprise-persons">
          <Route index element={<EnterprisePersonsPage />} />
          <Route path=":id" element={<EnterprisePersonDetailPage />} />
        </Route>
        <Route path="reference-data">
          <Route index element={<ReferenceDataPage />} />
          <Route path="new" element={<ReferenceCategoryNewPage />} />
          <Route path=":id" element={<ReferenceCategoryDetailPage />} />
        </Route>
        <Route path="reference-values">
          <Route index element={<ReferenceValuesPage />} />
          <Route path="new" element={<ReferenceValueNewPage />} />
          <Route path=":id" element={<ReferenceValueDetailPage />} />
        </Route>
        <Route path="search" element={<SearchPage />} />
        <Route path="master-records">
          <Route index element={<MasterRecordsPage />} />
          <Route path=":id">
            <Route index element={<MasterRecordDetailPage />} />
            <Route path="versions">
              <Route index element={<MasterRecordDetailPage />} />
              <Route path=":vid" element={<MasterRecordVersionDetailPage />} />
            </Route>
          </Route>
        </Route>
        <Route path="duplicates" element={<DuplicatesPage />} />
        <Route path="golden-records" element={<GoldenRecordsPage />} />
        <Route path="merges" element={<MergesPage />} />
        <Route path="approvals" element={<ApprovalsPage />} />
        <Route path="stewardship" element={<StewardshipPage />} />
        <Route path="imports" element={<ImportsPage />} />
        <Route path="exports" element={<ExportsPage />} />
        <Route path="integrations" element={<IntegrationsPage />} />
        <Route path="audit" element={<AuditPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  );
}
