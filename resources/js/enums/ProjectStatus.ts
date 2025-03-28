export enum ProjectStatus {
    Pending = 'pending',
    InProgress = 'in_progress',
    Completed = 'completed'
}

export const ProjectStatusLabels: Record<ProjectStatus, string> = {
    [ProjectStatus.Pending]: 'To Do',
    [ProjectStatus.InProgress]: 'In Progress',
    [ProjectStatus.Completed]: 'Done'
}

export const ProjectStatusValues = Object.values(ProjectStatus)
