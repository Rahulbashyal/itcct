<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class DockerService
{
    /**
     * Start a container for a workspace
     */
    public function startContainer($workspaceId, $projectPath)
    {
        $containerName = "nexus_workspace_{$workspaceId}";
        $fullPath = storage_path('app/' . $projectPath);

        // Check if container already exists
        $checkProcess = new Process(['docker', 'inspect', $containerName]);
        $checkProcess->run();

        if ($checkProcess->isSuccessful()) {
            $inspect = json_decode($checkProcess->getOutput(), true);
            $isRunning = $inspect[0]['State']['Running'];
            $portMapped = isset($inspect[0]['HostConfig']['PortBindings']['8080/tcp']);

            if ($isRunning && $portMapped) {
                return true;
            }

            // Container exists but needs restart or recreation (due to missing port mapping)
            (new Process(['docker', 'rm', '-f', $containerName]))->run();
        }

        // Create and start new container
        $process = new Process([
            'docker', 'run', '-d',
            '--name', $containerName,
            '-p', '8080:8080',
            '-v', "{$fullPath}:/workspace",
            '--memory', '512m',
            '--cpus', '0.5',
            'nexus-runtime',
            'tail', '-f', '/dev/null'
        ]);

        $process->run();
        return $process->isSuccessful();
    }

    /**
     * Execute a command in the container
     */
    public function execute($workspaceId, $command)
    {
        $containerName = "nexus_workspace_{$workspaceId}";
        
        $process = new Process(['docker', 'exec', $containerName, 'bash', '-c', $command]);
        $process->run();

        return [
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
            'exit_code' => $process->getExitCode()
        ];
    }

    /**
     * Stop a container
     */
    public function stopContainer($workspaceId)
    {
        $containerName = "nexus_workspace_{$workspaceId}";
        (new Process(['docker', 'stop', $containerName]))->run();
    }
}
