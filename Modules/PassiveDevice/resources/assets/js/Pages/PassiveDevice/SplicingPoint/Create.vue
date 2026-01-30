<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Switch } from '@/components/ui/switch'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, useForm } from '@inertiajs/vue3'

  defineProps<{
    areas: Array<any>
    jointBoxes: Array<any>
  }>()

  const form = useForm({
    infrastructure_area_id: '',
    joint_box_id: '',
    code: '',
    name: '',
    tray_number: '',
    splicing_type: '',
    status: 'Active',
    loss: 0,
    description: '',
    is_active: true,
    spliced_at: '',
  })

  const submit = () => {
    form.post(route('passive-device.splicing-point.store'))
  }
</script>

<template>
  <Head title="Add Splicing Point" />

  <AppLayout
    :breadcrumbs="[
      {
        title: 'Splicing Points',
        href: route('passive-device.splicing-point.index'),
      },
      { title: 'Add Splicing Point', href: '#' },
    ]">
    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Add Splicing Point</h1>
          <p class="text-muted-foreground">Register a new fiber splicing point.</p>
        </div>
      </div>

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Splicing Information</CardTitle>
            <CardDescription> Basic details and tray assignment. </CardDescription>
          </CardHeader>
          <CardContent class="grid gap-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="area">Infrastructure Area</Label>
                <Select v-model="form.infrastructure_area_id">
                  <SelectTrigger
                    :class="{
                      'border-destructive': form.errors.infrastructure_area_id,
                    }">
                    <SelectValue placeholder="Select Area" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                      {{ area.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.infrastructure_area_id" class="text-sm text-destructive">
                  {{ form.errors.infrastructure_area_id }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="joint_box">Joint Box</Label>
                <Select v-model="form.joint_box_id">
                  <SelectTrigger
                    :class="{
                      'border-destructive': form.errors.joint_box_id,
                    }">
                    <SelectValue placeholder="Select Joint Box" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="jb in jointBoxes" :key="jb.id" :value="jb.id.toString()">
                      {{ jb.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.joint_box_id" class="text-sm text-destructive">
                  {{ form.errors.joint_box_id }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="code">Code</Label>
                <Input
                  id="code"
                  v-model="form.code"
                  :class="{
                    'border-destructive': form.errors.code,
                  }"
                  placeholder="SP-XYZ" />
                <p v-if="form.errors.code" class="text-sm text-destructive">
                  {{ form.errors.code }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  :class="{
                    'border-destructive': form.errors.name,
                  }"
                  placeholder="Splicing Point A" />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                  {{ form.errors.name }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="tray_number">Tray Number</Label>
                <Input id="tray_number" v-model="form.tray_number" />
              </div>
              <div class="space-y-2">
                <Label for="splicing_type">Splicing Type</Label>
                <Select v-model="form.splicing_type">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Core-Core">Core-Core</SelectItem>
                    <SelectItem value="Pigtail-Core">Pigtail-Core</SelectItem>
                    <SelectItem value="Splitter-Core">Splitter-Core</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="status">Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Active">Active</SelectItem>
                    <SelectItem value="Spare">Spare</SelectItem>
                    <SelectItem value="Damaged">Damaged</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="loss">Loss (dB)</Label>
                <Input id="loss" type="number" step="0.01" v-model="form.loss" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="spliced_at">Spliced At</Label>
                <Input id="spliced_at" type="date" v-model="form.spliced_at" />
              </div>
              <div class="flex items-center space-x-2 pt-8">
                <Switch id="is_active" v-model:checked="form.is_active" />
                <Label for="is_active">Active Status</Label>
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea id="description" v-model="form.description" />
            </div>
          </CardContent>
          <CardFooter class="mt-6 flex justify-end gap-2 border-t p-6">
            <Button variant="outline" as-child>
              <Link :href="route('passive-device.splicing-point.index')">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : 'Save Splicing Point' }}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>
