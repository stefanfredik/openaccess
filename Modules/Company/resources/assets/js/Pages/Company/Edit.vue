<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Switch } from '@/components/ui/switch'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, useForm } from '@inertiajs/vue3'
  import { ChevronLeft, Save } from 'lucide-vue-next'
  // import { index as companiesIndex, edit as companiesEdit, update as companiesUpdate } from '@/routes/companies';

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
    }
    logo_url: string | null
  }>()

  const form = useForm({
    _method: 'PUT', // For file upload in update
    code: props.company.code,
    name: props.company.name,
    address: props.company.address,
    phone: props.company.phone,
    email: props.company.email,
    website: props.company.website,
    logo: null as File | null,
    is_active: Boolean(props.company.is_active),
  })

  const submit = () => {
    form.post(route('companies.update', props.company.id), {
      forceFormData: true,
    })
  }

  const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files[0]) {
      form.logo = target.files[0]
    }
  }
</script>

<template>
  <Head title="Edit Company" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Companies', href: route('companies.index') },
      { title: 'Edit', href: route('companies.edit', company.id) },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center gap-4">
        <Button variant="outline" size="icon" as-child>
          <Link :href="route('companies.index')">
            <ChevronLeft class="h-4 w-4" />
          </Link>
        </Button>
        <div class="flex-1">
          <h1 class="text-2xl font-bold tracking-tight">Edit Company</h1>
          <p class="text-muted-foreground">Update company information.</p>
        </div>
      </div>

      <div class="grid gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Company Details</CardTitle>
            <CardDescription> Change the information below. </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="code">Company Code</Label>
                  <Input
                    id="code"
                    v-model="form.code"
                    placeholder="e.g. VIS-001"
                    :class="{
                      'border-destructive': form.errors.code,
                    }" />
                  <span v-if="form.errors.code" class="text-xs text-destructive">
                    {{ form.errors.code }}
                  </span>
                </div>
                <div class="space-y-2">
                  <Label for="name">Company Name</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    placeholder="e.g. Vision Internet"
                    :class="{
                      'border-destructive': form.errors.name,
                    }" />
                  <span v-if="form.errors.name" class="text-xs text-destructive">
                    {{ form.errors.name }}
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="address">Address</Label>
                <Textarea
                  id="address"
                  v-model="form.address"
                  placeholder="Full office address"
                  :class="{
                    'border-destructive': form.errors.address,
                  }" />
                <span v-if="form.errors.address" class="text-xs text-destructive">
                  {{ form.errors.address }}
                </span>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="phone">Phone Number</Label>
                  <Input
                    id="phone"
                    v-model="form.phone"
                    placeholder="+62..."
                    :class="{
                      'border-destructive': form.errors.phone,
                    }" />
                  <span v-if="form.errors.phone" class="text-xs text-destructive">
                    {{ form.errors.phone }}
                  </span>
                </div>
                <div class="space-y-2">
                  <Label for="email">Email Address</Label>
                  <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="contact@company.com"
                    :class="{
                      'border-destructive': form.errors.email,
                    }" />
                  <span v-if="form.errors.email" class="text-xs text-destructive">
                    {{ form.errors.email }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="website">Website (Optional)</Label>
                  <Input
                    id="website"
                    v-model="form.website"
                    placeholder="https://..."
                    :class="{
                      'border-destructive': form.errors.website,
                    }" />
                  <span v-if="form.errors.website" class="text-xs text-destructive">
                    {{ form.errors.website }}
                  </span>
                </div>
                <div class="space-y-2">
                  <Label for="logo">Logo (Optional)</Label>
                  <div v-if="logo_url" class="mb-2">
                    <img :src="logo_url" alt="Current Logo" class="h-16 w-auto rounded border object-contain p-1" />
                  </div>
                  <Input id="logo" type="file" @change="handleLogoChange" accept="image/*" class="cursor-pointer" />
                  <span v-if="form.errors.logo" class="text-xs text-destructive">
                    {{ form.errors.logo }}
                  </span>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <Switch id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event" />
                <Label for="is_active">Active Status</Label>
              </div>

              <div class="flex justify-end gap-2">
                <Button variant="outline" as-child>
                  <Link :href="route('companies.index')">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                  <Save class="mr-2 h-4 w-4" />
                  Update Company
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
