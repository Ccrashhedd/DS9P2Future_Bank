package com.example.project2.ui.components

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.ErrorOutline
import androidx.compose.material.icons.outlined.FolderOff
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.TextPrimary
import com.example.project2.ui.theme.TextSecondary

@Composable
fun EmptyStateView(
    title: String,
    message: String,
    modifier: Modifier = Modifier,
    icon: ImageVector = Icons.Outlined.FolderOff
) {
    BankGlassCard(modifier = modifier.fillMaxWidth()) {
        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Icon(icon, contentDescription = null, tint = SilverDark)
            Spacer(Modifier.height(10.dp))
            Text(title, color = TextPrimary, style = MaterialTheme.typography.titleMedium)
            Text(message, color = TextSecondary, style = MaterialTheme.typography.bodyMedium)
        }
    }
}

@Composable
fun ErrorStateView(message: String, modifier: Modifier = Modifier) {
    BankGlassCard(modifier = modifier.fillMaxWidth()) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Icon(Icons.Outlined.ErrorOutline, contentDescription = null, tint = MaterialTheme.colorScheme.error)
            Spacer(Modifier.height(8.dp))
            Text("No se pudo cargar", color = TextPrimary, style = MaterialTheme.typography.titleMedium)
            Text(message, color = TextSecondary, style = MaterialTheme.typography.bodyMedium)
        }
    }
}

@Composable
fun LoadingView(message: String = "Cargando informacion...", modifier: Modifier = Modifier) {
    Column(
        modifier = modifier.fillMaxWidth(),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        CircularProgressIndicator(color = PrimaryPurple)
        Spacer(Modifier.height(12.dp))
        Text(message, color = TextSecondary)
    }
}
