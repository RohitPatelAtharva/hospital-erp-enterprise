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
import type { CreateOrganizationPayload, OrganizationResponse } from '@/lib/organization-types';

interface FieldErrors {
  name?: string;
  organization_type_code?: string;
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

export function OrganizationNewPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [form, setForm] = useState<CreateOrganizationPayload>({ name: '' });
  const [errors, setErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);

  function setField<K extends keyof CreateOrganizationPayload>(key: K, value: CreateOrganizationPayload[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
    setErrors((prev) => ({ ...prev, [key]: undefined }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      const res = await api.post<OrganizationResponse>('/organizations', form);
      toast({ title: 'Organization added', description: 'The organization record was created successfully.', variant: 'success' });
      navigate(`/organizations/${res.data.id}`);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setErrors(extractFieldErrors(err));
        toast({ title: 'Validation failed', description: 'Please correct the highlighted fields.', variant: 'destructive' });
      } else {
        const message = err instanceof ApiError ? err.message : 'Failed to add organization.';
        toast({ title: 'Registration failed', description: message, variant: 'destructive' });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <PageContainer>
      <PageHeader
        title="Add Organization"
        description="Create a new organization master data record"
        crumbs={[{ label: 'Master Data' }, { label: 'Organizations', href: '/organizations' }, { label: 'Add Organization' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/organizations">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Organizations
            </Link>
          </Button>
        }
      />

      <form onSubmit={onSubmit} className="space-y-4" noValidate>
        {/* Organization Identity */}
        <Card>
          <CardHeader>
            <CardTitle>Organization Identity</CardTitle>
            <CardDescription>Core identifying fields for the organization.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <FormField
              id="name"
              label="Organization name"
              error={errors.name}
              hint="Required. Maximum 120 characters."
            >
              <Input
                id="name"
                value={form.name}
                onChange={(e) => setField('name', e.target.value)}
                aria-invalid={Boolean(errors.name)}
                aria-describedby={errors.name ? 'name-error' : undefined}
                autoComplete="organization"
              />
            </FormField>
          </CardContent>
        </Card>

        {/* Organization Classification */}
        <Card>
          <CardHeader>
            <CardTitle>Organization Classification</CardTitle>
            <CardDescription>Optional type code used to classify the organization.</CardDescription>
          </CardHeader>
          <CardContent>
            <FormField
              id="organization_type_code"
              label="Organization type code"
              error={errors.organization_type_code}
              hint="Optional. Maximum 40 characters."
            >
              <Input
                id="organization_type_code"
                value={form.organization_type_code ?? ''}
                onChange={(e) => setField('organization_type_code', e.target.value || null)}
                aria-invalid={Boolean(errors.organization_type_code)}
                aria-describedby={errors.organization_type_code ? 'organization_type_code-error' : undefined}
              />
            </FormField>
          </CardContent>
        </Card>

        {/* Additional Information */}
        <Card>
          <CardHeader>
            <CardTitle>Additional Information</CardTitle>
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
          <Button type="button" variant="outline" onClick={() => navigate('/organizations')}>
            Cancel
          </Button>
          <Button type="submit" disabled={submitting}>
            {submitting && <Loader2 className="size-4 animate-spin" aria-hidden />}
            {submitting ? 'Adding…' : 'Add Organization'}
          </Button>
        </div>
      </form>
    </PageContainer>
  );
}
