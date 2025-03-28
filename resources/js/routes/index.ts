import { NavItem } from "@/types";
import { LayoutGrid, BookA, Activity, Calendar1, BabyIcon } from "lucide-vue-next";


export const SideBarMainItems : NavItem[] = [
    {
        title: 'dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,

    },
    {
        title: 'Projects',
        href: route('projects.index'),
        icon: BookA,
    },
    {
        title: 'Tasks',
        href: route('tasks.index'),
        icon: Activity
    },
    {
        title: 'Calendar',
        href: route('calendar.index'),
        icon: Calendar1
    },
    {
        title: 'Users',
        href: route('users.index'),
        icon: BabyIcon
    }
];
