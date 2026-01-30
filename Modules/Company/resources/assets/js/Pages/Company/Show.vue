<script setup lang="ts">
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, usePage } from '@inertiajs/vue3'
  import { Building2, ChevronLeft, Globe, Mail, MapPin, Pencil, Phone } from 'lucide-vue-next'
  import { computed } from 'vue'
  // import { index as companiesIndex, edit as companiesEdit } from '@/routes/companies';

  const page = usePage()
  const isSuperAdmin = computed(() => (page.props.auth as any)?.roles?.includes('superadmin') || false)

  const props = defineProps<{
    company: {
      id: number
      code: string
      name: string
      address: string
      phone: string
      email: string
      website: string | null
      logo: string | null
      is_active: boolean
      created_at: string
      updated_at: string
    }
  }>()
</script>

<template>
  <Head :title="company.name" />

  <AppLayout
    :breadcrumbs="
      [isSuperAdmin ? { title: 'Companies', href: route('companies.index') } : null, { title: company.name, href: '' }].filter(Boolean) as any
    ">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center gap-4">
        <Button v-if="isSuperAdmin" variant="outline" size="icon" as-child>
          <Link :href="route('companies.index')">
            <ChevronLeft class="h-4 w-4" />
          </Link>
        </Button>
        <div class="flex-1">
          <h1 class="text-2xl font-bold tracking-tight">
            {{ company.name }}
          </h1>
          <div class="flex items-center gap-2 text-muted-foreground">
            <span class="rounded bg-muted px-2 py-0.5 font-mono text-sm">{{ company.code }}</span>
            <Badge :variant="company.is_active ? 'default' : 'secondary'">
              {{ company.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </div>
        </div>
        <Button v-if="isSuperAdmin" as-child>
          <Link :href="route('companies.edit', company.id)">
            <Pencil class="mr-2 h-4 w-4" />
            Edit Company
          </Link>
        </Button>
      </div>

      <div class="grid gap-6 md:grid-cols-3">
        <!-- Main Info Card -->
        <Card class="md:col-span-2">
          <CardHeader>
            <CardTitle>Company Information</CardTitle>
            <CardDescription>Basic details about the company.</CardDescription>
          </CardHeader>
          <CardContent class="grid gap-6">
            <div class="flex items-start gap-4">
              <div class="flex h-24 w-24 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted">
                <img v-if="company.logo" :src="`/storage/${company.logo}`" :alt="company.name" class="h-full w-full object-cover" />
                <Building2 v-else class="h-10 w-10 text-muted-foreground" />
              </div>
              <div class="flex-1 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <div class="mb-1 text-sm font-medium text-muted-foreground">Company Name</div>
                    <div class="font-medium">
                      {{ company.name }}
                    </div>
                  </div>
                  <div>
                    <div class="mb-1 text-sm font-medium text-muted-foreground">Company Code</div>
                    <div class="font-medium">
                      {{ company.code }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 border-t pt-4 md:grid-cols-2">
              <div class="space-y-3">
                <div class="flex items-center gap-2 text-muted-foreground">
                  <Mail class="h-4 w-4" />
                  <span class="text-sm font-medium">Contact Email</span>
                </div>
                <div class="pl-6">
                  <a :href="`mailto:${company.email}`" class="text-primary hover:underline">
                    {{ company.email }}
                  </a>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-2 text-muted-foreground">
                  <Phone class="h-4 w-4" />
                  <span class="text-sm font-medium">Phone Number</span>
                </div>
                <div class="pl-6">
                  <a :href="`tel:${company.phone}`" class="text-primary hover:underline">
                    {{ company.phone }}
                  </a>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-2 text-muted-foreground">
                  <Globe class="h-4 w-4" />
                  <span class="text-sm font-medium">Website</span>
                </div>
                <div class="pl-6">
                  <a v-if="company.website" :href="company.website" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                    {{ company.website }}
                  </a>
                  <span v-else class="text-sm text-muted-foreground italic"> Not provided </span>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-2 text-muted-foreground">
                  <MapPin class="h-4 w-4" />
                  <span class="text-sm font-medium">Address</span>
                </div>
                <div class="pl-6 text-sm leading-relaxed whitespace-pre-wrap">
                  {{ company.address }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Sidebar Metadata Card -->
        <Card>
          <CardHeader>
            <CardTitle>System Metadata</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4">
            <div class="space-y-1">
              <div class="text-sm text-muted-foreground">Created At</div>
              <div class="text-sm font-medium">
                {{
                  new Date(company.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })
                }}
              </div>
            </div>
            <div class="space-y-1">
              <div class="text-sm text-muted-foreground">Last Updated</div>
              <div class="text-sm font-medium">
                {{
                  new Date(company.updated_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })
                }}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
