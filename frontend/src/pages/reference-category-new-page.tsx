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
import type { CreateReferenceCategoryPayload, ReferenceCategoryResponse } from '@/lib/reference-data-types';

export function ReferenceCategoryNewPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [form, setForm] = useState<CreateReferenceCategoryPayload>({ code: '' });
  const [errors, setErrors] = useState<{ code?: string }>({});
  const [submitting, setSubmitting] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      const res = await api.post<ReferenceCategoryResponse>('/reference-categories', form);
      toast({ title: 'Category created', description: 'The reference category was created successfully.', variant: 'success' });
      navigate(`/reference-data/${res.data.id}`);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setErrors((err.errors ?? {}) as { code?: string });
        toast({ title: 'Validation failed', description: 'Please correct the highlighted fields.', variant: 'destructive' });
      } else {
        const message = err instanceof ApiError ? err.message : 'Failed to create category.';
        toast({ title: 'Creation failed', description: message, variant: 'destructive' });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <PageContainer>
      <PageHeader
        title="Create Reference Category"
        description="Define a new canonical reference category"
        crumbs={[{ label: 'Master Data' }, { label: 'Reference Data', href: '/reference-data' }, { label: 'Create Category' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/reference-data">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Reference Data
            </Link>
          </Button>
        }
      />

      <form onSubmit={onSubmit} className="max-w-2xl" noValidate>
        <Card>
          <CardHeader>
            <CardTitle>Category Information</CardTitle>
            <CardDescription>Enter the unique code that identifies this reference category.</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-1.5">
              <Label htmlFor="code">Code</Label>
              <Input
                id="code"
                value={form.code}
                onChange={(e) => setForm((p) => ({ ...p, code: e.target.value }))}
                aria-invalid={Boolean(errors.code)}
                aria-describedby={errors.code ? 'code-error' : undefined}
                autoComplete="off"
                placeholder="e.g. GENDER"
              />
              {errors.code ? (
                <p id="code-error" role="alert" className="text-destructive text-xs">
                  {errors.code}
                </p>
              ) : (
                <p className="text-muted-foreground text-xs">Required. Maximum 60 characters.</p>
              )}
            </div>
          </CardContent>
        </Card>

        <div className="mt-4 flex justify-end gap-2">
          <Button type="button" variant="outline" onClick={() => navigate('/reference-data')}>
            Cancel
          </Button>
          <Button type="submit" disabled={submitting}>
            {submitting && <Loader2 className="size-4 animate-spin" aria-hidden />}
            {submitting ? 'Creating…' : 'Create Category'}
          </Button>
        </div>
      </form>
    </PageContainer>
  );
}
