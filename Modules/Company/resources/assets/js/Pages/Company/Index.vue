<script setup lang="ts">
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, router, usePage } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Building2, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next'
  import { computed, ref, watch } from 'vue'

  const page = usePage()
  const isSuperAdmin = computed(() => (page.props.auth as any)?.roles?.includes('superadmin') || false)

  const props = defineProps<{
    companies: {
      data: Array<{
        id: number
        code: string
        name: string
        phone: string
        email: string
        is_active: boolean
        logo: string | null
      }>
      links: any[]
      current_page: number
      last_page: number
    }
    filters: {
      search?: string
    }
  }>()

  const search = ref(props.filters.search || '')

  watch(
    search,
    debounce((value: string) => {
      router.get(route('companies.index'), { search: value }, { preserveState: true, replace: true })
    }, 300),
  )

  const deleteCompany = (id: number) => {
    if (confirm('Are you sure you want to delete this company?')) {
      router.delete(route('companies.destroy', id))
    }
  }
</script>

<template>
  <Head title="Companies" />

  <AppLayout :breadcrumbs="[{ title: 'Companies', href: route('companies.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Companies</h1>
          <p class="text-muted-foreground">Manage your ISP companies.</p>
        </div>
        <Button v-if="isSuperAdmin" as-child>
          <Link :href="route('companies.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add Company
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
          <div class="space-y-1">
            <CardTitle>List of Companies</CardTitle>
            <CardDescription> View and manage registered companies. </CardDescription>
          </div>
          <div class="w-full max-w-sm items-center space-x-2">
            <div class="relative">
              <Search class="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
              <Input v-model="search" placeholder="Search companies..." class="pl-8" />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[80px]">Logo</TableHead>
                  <TableHead>Code</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Contact</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead v-if="isSuperAdmin" class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="companies.data.length === 0">
                  <TableCell colspan="6" class="h-24 text-center text-muted-foreground"> No companies found. </TableCell>
                </TableRow>
                <TableRow v-for="company in companies.data" :key="company.id">
                  <TableCell>
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border bg-muted">
                      <img v-if="company.logo" :src="`/storage/${company.logo}`" alt="Logo" class="h-full w-full object-cover" />
                      <Building2 v-else class="h-5 w-5 text-muted-foreground" />
                    </div>
                  </TableCell>
                  <TableCell class="font-medium">{{ company.code }}</TableCell>
                  <TableCell>
                    <Link :href="route('companies.show', company.id)" class="font-medium hover:underline">
                      {{ company.name }}
                    </Link>
                  </TableCell>
                  <TableCell>
                    <div class="flex flex-col text-sm">
                      <span>{{ company.email }}</span>
                      <span class="text-xs text-muted-foreground">{{ company.phone }}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="company.is_active ? 'default' : 'secondary'">
                      {{ company.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell v-if="isSuperAdmin" class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('companies.edit', company.id)">
                          <Pencil class="h-4 w-4" />
                        </Link>
                      </Button>
                      <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" @click="deleteCompany(company.id)">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-end space-x-2 py-4" v-if="companies.links.length > 3">
            <template v-for="(link, i) in companies.links" :key="i">
              <Button v-if="link.url || link.label" :variant="link.active ? 'default' : 'outline'" size="sm" :disabled="!link.url" as-child>
                <Link v-if="link.url" :href="link.url">
                  <span v-html="link.label"></span>
                </Link>
                <span v-else v-html="link.label"></span>
              </Button>
            </template>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
