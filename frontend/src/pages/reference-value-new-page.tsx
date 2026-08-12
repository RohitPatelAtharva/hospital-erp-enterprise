import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Loader2 } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api-client';
import { useToast } from '@/hooks/use-toast';
import type { CreateReferenceValuePayload, ReferenceValueResponse } from '@/lib/reference-data-types';

interface FieldErrors {
  code?: string;
  category_code?: string;
  version_code?: string;
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

export function ReferenceValueNewPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [form, setForm] = useState<CreateReferenceValuePayload>({ code: '', category_code: '' });
  const [errors, setErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);

  function setField<K extends keyof CreateReferenceValuePayload>(key: K, value: CreateReferenceValuePayload[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
    setErrors((prev) => ({ ...prev, [key]: undefined }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      const res = await api.post<ReferenceValueResponse>('/reference-values', form);
      toast({ title: 'Value added', description: 'The reference value was created successfully.', variant: 'success' });
      navigate(`/reference-values/${res.data.id}`);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setErrors((err.errors ?? {}) as FieldErrors);
        toast({ title: 'Validation failed', description: 'Please correct the highlighted fields.', variant: 'destructive' });
      } else {
        const message = err instanceof ApiError ? err.message : 'Failed to add value.';
        toast({ title: 'Creation failed', description: message, variant: 'destructive' });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <PageContainer>
      <PageHeader
        title="Add Reference Value"
        description="Create a new canonical reference value under a category"
        crumbs={[{ label: 'Master Data' }, { label: 'Reference Values', href: '/reference-values' }, { label: 'Add Value' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/reference-values">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Reference Values
            </Link>
          </Button>
        }
      />

      <form onSubmit={onSubmit} className="max-w-2xl space-y-4" noValidate>
        <Card>
          <CardHeader>
            <CardTitle>Value Information</CardTitle>
            <CardDescription>Enter the value and the category it belongs to.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <FormField id="code" label="Value code" error={errors.code} hint="Required. Maximum 40 characters.">
              <Input
                id="code"
                value={form.code}
                onChange={(e) => setField('code', e.target.value)}
                aria-invalid={Boolean(errors.code)}
                aria-describedby={errors.code ? 'code-error' : undefined}
                autoComplete="off"
              />
            </FormField>
            <FormField
              id="category_code"
              label="Category code"
              error={errors.category_code}
              hint="Required. The code of the reference category this value belongs to (e.g. GENDER)."
            >
              <Input
                id="category_code"
                value={form.category_code}
                onChange={(e) => setField('category_code', e.target.value)}
                aria-invalid={Boolean(errors.category_code)}
                aria-describedby={errors.category_code ? 'category_code-error' : undefined}
                autoComplete="off"
              />
            </FormField>
            <FormField
              id="version_code"
              label="Version code"
              error={errors.version_code}
              hint="Optional. The reference version code to pin this value to. Maximum 40 characters."
            >
              <Input
                id="version_code"
                value={form.version_code ?? ''}
                onChange={(e) => setField('version_code', e.target.value || null)}
                aria-invalid={Boolean(errors.version_code)}
                aria-describedby={errors.version_code ? 'version_code-error' : undefined}
                autoComplete="off"
              />
            </FormField>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-2">
          <Button type="button" variant="outline" onClick={() => navigate('/reference-values')}>
            Cancel
          </Button>
          <Button type="submit" disabled={submitting}>
            {submitting && <Loader2 className="size-4 animate-spin" aria-hidden />}
            {submitting ? 'Adding…' : 'Add Value'}
          </Button>
        </div>
      </form>
    </PageContainer>
  );
}
