import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, RefreshCw } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { EmptyState } from '@/components/feedback/empty-state';
import { PatientStatusBadge } from '@/components/patients/patient-status-badge';
import { PatientLifecycleActions } from '@/components/patients/patient-lifecycle-actions';
import { usePatient } from '@/hooks/use-patient';
import { formatDate, formatDateTime } from '@/lib/utils';
import type {
  PatientAlias,
  PatientChildResource,
  PatientConsent,
  PatientDemographic,
  PatientIdentifier,
  PatientRelation,
} from '@/lib/patient-types';

const TABS: { key: PatientChildResource; label: string }[] = [
  { key: 'identifiers', label: 'Identifiers' },
  { key: 'demographics', label: 'Demographics' },
  { key: 'consents', label: 'Consents' },
  { key: 'relations', label: 'Relations' },
  { key: 'aliases', label: 'Aliases' },
];

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
  );
}

function ChildTable({ children }: { children: React.ReactNode }) {
  return (
    <div className="rounded-md border">
      <table className="w-full text-sm">
        <tbody className="divide-y">{children}</tbody>
      </table>
    </div>
  );
}

function ChildRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <tr>
      <th scope="row" className="text-muted-foreground w-1/3 px-4 py-2.5 text-left font-normal">
        {label}
      </th>
      <td className="px-4 py-2.5 text-right font-medium">{value}</td>
    </tr>
  );
}

export function PatientDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<PatientChildResource>('identifiers');

  const { patient, child, loading, error, refresh, acting, actionError, runLifecycle } = usePatient(id);

  if (!id) {
    return <ErrorState title="Missing patient ID" message="No patient identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Patient Details"
        description={patient ? `Patient ${patient.name ?? ''}`.trim() : 'Patient record'}
        crumbs={[{ label: 'Master Data' }, { label: 'Patients', href: '/patients' }, { label: 'Patient Details' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/patients">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Patients
            </Link>
          </Button>
        }
      />

      {loading ? (
        <Card>
          <CardHeader>
            <Skeleton className="h-6 w-48" />
          </CardHeader>
          <CardContent className="space-y-4">
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-2/3" />
          </CardContent>
        </Card>
      ) : error ? (
        <ErrorState title="Unable to load patient" message={error} onRetry={refresh} />
      ) : patient ? (
        <>
          <div className="grid gap-4 lg:grid-cols-3">
            {/* Summary */}
            <Card className="lg:col-span-2">
              <CardHeader className="flex-row items-start justify-between gap-4">
                <div className="space-y-1">
                  <CardTitle>{patient.name ?? 'Unnamed patient'}</CardTitle>
                  <CardDescription>Patient master record</CardDescription>
                </div>
                <PatientStatusBadge status={patient.status} />
              </CardHeader>
              <CardContent>
                <dl className="divide-y">
                  <SummaryRow label="ID" value={<span className="font-mono text-xs">{patient.id}</span>} />
                  <SummaryRow label="Date of birth" value={formatDate(patient.dob)} />
                  <SummaryRow label="Sex" value={<span className="capitalize">{patient.sex ?? '—'}</span>} />
                  <SummaryRow label="Version" value={patient.version} />
                  <SummaryRow label="Created" value={formatDateTime(patient.created_at)} />
                  <SummaryRow label="Updated" value={formatDateTime(patient.updated_at)} />
                </dl>
              </CardContent>
            </Card>

            {/* Lifecycle */}
            <Card>
              <CardHeader>
                <CardTitle>Record Actions</CardTitle>
                <CardDescription>Manage the lifecycle state of this patient record.</CardDescription>
              </CardHeader>
              <CardContent>
                <PatientLifecycleActions
                  patient={patient}
                  acting={acting}
                  actionError={actionError}
                  onAction={runLifecycle}
                />
                <Button variant="outline" size="sm" className="mt-3" onClick={refresh} disabled={acting} aria-label="Refresh patient data">
                  <RefreshCw className="size-4" aria-hidden />
                  Refresh
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Child resources */}
          <Card>
            <CardHeader className="pb-0">
              <div role="tablist" aria-label="Patient sections" className="flex flex-wrap gap-1">
                {TABS.map((tab) => (
                  <button
                    key={tab.key}
                    role="tab"
                    aria-selected={activeTab === tab.key}
                    aria-controls={`tab-${tab.key}`}
                    id={`tab-button-${tab.key}`}
                    onClick={() => setActiveTab(tab.key)}
                    className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors focus-visible:ring-ring focus-visible:ring-2 focus-visible:outline-none ${
                      activeTab === tab.key
                        ? 'bg-accent text-accent-foreground'
                        : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground'
                    }`}
                  >
                    {tab.label}
                  </button>
                ))}
              </div>
            </CardHeader>
            <CardContent>
              <div role="tabpanel" id={`tab-${activeTab}`} aria-labelledby={`tab-button-${activeTab}`}>
                <ChildResourcePanel resource={activeTab} data={child[activeTab]} />
              </div>
            </CardContent>
          </Card>
        </>
      ) : null}
    </PageContainer>
  );
}

function ChildResourcePanel({
  resource,
  data,
}: {
  resource: PatientChildResource;
  data:
    | PatientIdentifier[]
    | PatientDemographic[]
    | PatientConsent[]
    | PatientRelation[]
    | PatientAlias[]
    | undefined;
}) {
  if (!data || data.length === 0) {
    return <EmptyState title="No records" description={`No ${resource} exist for this patient.`} className="py-8" />;
  }

  return (
    <div className="space-y-2">
      {resource === 'identifiers' &&
        (data as PatientIdentifier[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Identity type" value={<span className="font-mono text-xs">{item.identity_type_id}</span>} />
            <ChildRow label="Value" value={item.value} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'demographics' &&
        (data as PatientDemographic[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Ethnicity" value={item.ethnicity ?? '—'} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'consents' &&
        (data as PatientConsent[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Consent type" value={<span className="font-mono text-xs">{item.consent_type_id}</span>} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'relations' &&
        (data as PatientRelation[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Related patient" value={<span className="font-mono text-xs">{item.related_patient_id}</span>} />
            <ChildRow label="Relation type" value={<span className="font-mono text-xs">{item.relation_type_id}</span>} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'aliases' &&
        (data as PatientAlias[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Alias name" value={item.name} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
    </div>
  );
}
