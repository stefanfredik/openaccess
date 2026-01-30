export interface Company {
  id: number
  name: string
  code: string | null
}

export interface InfrastructureArea {
  id: number
  name: string
  code: string | null
  type: string | null
}

export interface Pop {
  id: number
  name: string
  code: string | null
  status: string | null
}

export interface PaginationLinks {
  url: string | null
  label: string
  active: boolean
}

export interface PaginatedData<T> {
  data: T[]
  current_page: number
  first_page_url: string
  from: number | null
  last_page: number
  last_page_url: string
  links: PaginationLinks[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}

export interface DeviceInterface {
  id: number
  name: string
  type: string
  status: 'Up' | 'Down'
  description?: string
}

export interface ActiveDevice {
  id: number
  company_id: number
  infrastructure_area_id: number
  pop_id: number | null
  code: string
  name: string
  brand: string | null
  model: string | null
  ip_address: string | null
  status: string
  installed_at: string | null
  area?: InfrastructureArea
  pop?: Pop
  interfaces?: DeviceInterface[]
}

export interface Router extends ActiveDevice {
  port_count: number
}

export interface Olt extends ActiveDevice {
  pon_type: string | null
}

export interface Ont extends ActiveDevice {
  onu_type: string | null
}

export interface AdSwitch extends ActiveDevice {
  port_count: number
  switch_type: string | null
}

export interface AccessPoint extends ActiveDevice {
  ssid_count: number
  frequency: string | null
}

export interface Cpe extends ActiveDevice {
  type: string | null
}

export interface Server extends ActiveDevice {
  os: string | null
  cpu: string | null
  ram: string | null
  storage: string | null
}

export interface Odp {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  splitter_type: string | null
  port_count: number
  used_port_count: number
  area?: InfrastructureArea
}

export interface Pole {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  material: string
  ownership: string
  height: number
  area?: InfrastructureArea
}

export interface JointBox {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  capacity: number
  used_capacity: number
  area?: InfrastructureArea
}

export interface SplicingPoint {
  id: number
  infrastructure_area_id: number
  ad_joint_box_id: number | null
  code: string | null
  name: string
  area?: InfrastructureArea
  jointBox?: JointBox
}

export interface Odf {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  port_count: number
  area?: InfrastructureArea
}

export interface Cable {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  type: string
  core_count: number
  length: number
  area?: InfrastructureArea
}

export interface Slack {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  length: number
  area?: InfrastructureArea
}

export interface Splitter {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  splitter_type: string
  port_count: number
  area?: InfrastructureArea
}

export interface Tower {
  id: number
  infrastructure_area_id: number
  code: string | null
  name: string
  type: string
  height: number
  ownership: string
  area?: InfrastructureArea
}
