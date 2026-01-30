<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { index as userIndex, edit as userEdit, update as userUpdate } from '@/routes/user'
  import { Head, Link, useForm } from '@inertiajs/vue3'

  const props = defineProps<{
    user: any
    roles: Array<any>
    companies: Array<any>
  }>()

  const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    company_id: props.user.company_id,
  })

  const submit = () => {
    form.put(userUpdate({ user: props.user.id }).url)
  }
</script>

<template>
  <Head title="Edit User" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Users', href: userIndex().url },
      { title: 'Edit', href: userEdit({ user: user.id }).url },
    ]">
    <div class="max-w-2xl mx-auto p-4 md:p-6">
      <Card>
        <CardHeader>
          <CardTitle>Edit User</CardTitle>
          <CardDescription>Update user details.</CardDescription>
        </CardHeader>
        <form @submit.prevent="submit">
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label for="name">Name</Label>
              <Input id="name" v-model="form.name" required />
              <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
            </div>

            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input id="email" type="email" v-model="form.email" required />
              <div v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="password">Password (Optional)</Label>
                <Input id="password" type="password" v-model="form.password" placeholder="Leave blank to keep current" />
                <div v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</div>
              </div>
              <div class="space-y-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                <div v-if="form.errors.password_confirmation" class="text-sm text-destructive">{{ form.errors.password_confirmation }}</div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="role">Role</Label>
                <Select v-model="form.role">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Role" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                      {{ role.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</div>
              </div>
              <div class="space-y-2">
                <Label for="company">Company (Optional)</Label>
                <Select v-model="form.company_id">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Company" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="company in companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.company_id" class="text-sm text-destructive">{{ form.errors.company_id }}</div>
              </div>
            </div>
          </CardContent>
          <CardFooter class="flex justify-between">
            <Button variant="outline" as-child>
              <Link :href="userIndex().url">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">Update User</Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
