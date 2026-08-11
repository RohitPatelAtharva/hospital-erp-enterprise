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
import { ReferenceDataPage } from '@/pages/reference-data-page';
import { SearchPage } from '@/pages/search-page';
import { MasterRecordsPage } from '@/pages/master-records-page';
import { VersionsPage } from '@/pages/versions-page';
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
        <Route path="reference-data" element={<ReferenceDataPage />} />
        <Route path="search" element={<SearchPage />} />
        <Route path="master-records" element={<MasterRecordsPage />} />
        <Route path="versions" element={<VersionsPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  );
}
