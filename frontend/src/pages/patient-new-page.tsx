import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Loader2 } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { api, ApiError } from '@/lib/api-client';
import { useToast } from '@/hooks/use-toast';
import type { CreatePatientPayload, PatientResponse } from '@/lib/patient-types';

interface FieldErrors {
  name?: string;
  dob?: string;
  sex?: string;
  external_ref?: string;
}

function extractFieldErrors(err: unknown): FieldErrors {
  if (err instanceof ApiError && err.errors) {
    return err.errors as FieldErrors;
  }
  return {};
}

function FormField({
  id,
  label,
  error,
  hint,
  children,
}: {
  id: string;
  label: string;
  error?: string;
  hint?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>
      {children}
      {error ? (
        <p id={`${id}-error`} role="alert" className="text-destructive text-xs">
          {error}
        </p>
      ) : hint ? (
        <p className="text-muted-foreground text-xs">{hint}</p>
      ) : null}
    </div>
  );
}

export function PatientNewPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [form, setForm] = useState<CreatePatientPayload>({ name: '' });
  const [errors, setErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);

  function setField<K extends keyof CreatePatientPayload>(key: K, value: CreatePatientPayload[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
    setErrors((prev) => ({ ...prev, [key]: undefined }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      const res = await api.post<PatientResponse>('/patients', form);
      toast({ title: 'Patient registered', description: 'The patient record was created successfully.', variant: 'success' });
      navigate(`/patients/${res.data.id}`);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setErrors(extractFieldErrors(err));
        toast({ title: 'Validation failed', description: 'Please correct the highlighted fields.', variant: 'destructive' });
      } else {
        const message = err instanceof ApiError ? err.message : 'Failed to register patient.';
        toast({ title: 'Registration failed', description: message, variant: 'destructive' });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <PageContainer>
      <PageHeader
        title="Register Patient"
        description="Create a new patient master data record"
        crumbs={[{ label: 'Master Data' }, { label: 'Patients', href: '/patients' }, { label: 'Register Patient' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/patients">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Patients
            </Link>
          </Button>
        }
      />

      <form onSubmit={onSubmit} className="space-y-4" noValidate>
        {/* Identity */}
        <Card>
          <CardHeader>
            <CardTitle>Identity</CardTitle>
            <CardDescription>Core identifying fields for the patient.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <FormField
              id="name"
              label="Full name"
              error={errors.name}
              hint="Required. Maximum 120 characters."
            >
              <Input
                id="name"
                value={form.name}
                onChange={(e) => setField('name', e.target.value)}
                aria-invalid={Boolean(errors.name)}
                aria-describedby={errors.name ? 'name-error' : undefined}
                autoComplete="name"
              />
            </FormField>
          </CardContent>
        </Card>

        {/* Demographics */}
        <Card>
          <CardHeader>
            <CardTitle>Demographics</CardTitle>
            <CardDescription>Optional demographic information.</CardDescription>
          </CardHeader>
          <CardContent className="grid gap-4 sm:grid-cols-2">
            <FormField id="dob" label="Date of birth" error={errors.dob}>
              <Input
                id="dob"
                type="date"
                value={form.dob ?? ''}
                onChange={(e) => setField('dob', e.target.value || null)}
                aria-invalid={Boolean(errors.dob)}
                aria-describedby={errors.dob ? 'dob-error' : undefined}
              />
            </FormField>
            <FormField
              id="sex"
              label="Sex"
              error={errors.sex}
              hint="Optional. Maximum 20 characters."
            >
              <Input
                id="sex"
                value={form.sex ?? ''}
                onChange={(e) => setField('sex', e.target.value || null)}
                aria-invalid={Boolean(errors.sex)}
                aria-describedby={errors.sex ? 'sex-error' : undefined}
              />
            </FormField>
          </CardContent>
        </Card>

        {/* Additional information */}
        <Card>
          <CardHeader>
            <CardTitle>Additional information</CardTitle>
            <CardDescription>Optional external reference for the master record.</CardDescription>
          </CardHeader>
          <CardContent>
            <FormField
              id="external_ref"
              label="External reference"
              error={errors.external_ref}
              hint="Optional. Maximum 100 characters."
            >
              <Input
                id="external_ref"
                value={form.external_ref ?? ''}
                onChange={(e) => setField('external_ref', e.target.value || null)}
                aria-invalid={Boolean(errors.external_ref)}
                aria-describedby={errors.external_ref ? 'external_ref-error' : undefined}
              />
            </FormField>
          </CardContent>
        </Card>

        <Separator />

        <div className="flex justify-end gap-2">
          <Button type="button" variant="outline" onClick={() => navigate('/patients')}>
            Cancel
          </Button>
          <Button type="submit" disabled={submitting}>
            {submitting && <Loader2 className="size-4 animate-spin" aria-hidden />}
            {submitting ? 'Registering…' : 'Register Patient'}
          </Button>
        </div>
      </form>
    </PageContainer>
  );
}
