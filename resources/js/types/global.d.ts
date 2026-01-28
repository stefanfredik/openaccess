
// Ziggy route helper type definition
declare function route(name?: string, params?: any, absolute?: boolean, config?: any): string;

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: (name?: string, params?: any, absolute?: boolean, config?: any) => string;
    }
}
